<?php

class VkCommand extends CConsoleCommand
{

    public $access_token = false;
    public $owner_id = false;

    public function actionWhoHasSubjectTomorrow($subject_id)
    {
        $semester = Semesters::model()->actual();
        if (!$semester)
            throw new CException('Нет семестра');
        $tomorrow_date = (new DateTime())->add(new DateInterval("P1D"))
            ->format('Y-m-d');
        $tomorrow_time = strtotime($tomorrow_date);
        if (Holiday::model()->findByAttributes(['date' => $tomorrow_date]) || date('N', $tomorrow_time) == 7)
            throw new CException('Завтра выходной');
        $week_number = (($semester->week_number + (date('W', $tomorrow_time) - date('W', strtotime($semester->start_date)))) % 2) ? 1 : 2;
        $week_day = date('N', $tomorrow_time);
        /** @var Subjects $subject */
        $subject = Subjects::model()->findByPk($subject_id);
        if (!$subject)
            throw new CException('Нет такого предмета');
        /** @var GroupReplace[] $replaces */
        $replaces = $subject->getRelated('replaces', true, [
            'with' => 'group',
            'condition' => 'date = :tomorrow_date',
            'params' => [':tomorrow_date' => $tomorrow_date]
        ]);
        /** @var ScheduleElement[] $schedule_elements */
        $schedule_elements = $subject->getRelated('schedule_elements', true, [
            'with' => 'group',
            'condition' => 'week_number = :week_number AND week_day = :week_day AND semester_id = :semester_id',
            'params' => [':week_number' => $week_number, ':week_day' => $week_day, ":semester_id" => $semester->id]
        ]);
        $schedule = [];
        if ($schedule_elements)
            foreach ($schedule_elements as $schedule_element) {
                /** @var GroupReplace $replace */
                if (($replace = GroupReplace::model()->findByAttributes(['date' => $tomorrow_date, 'number' => $schedule_element->number, 'group_id' => $schedule_element->group_id])))
                    if ($replace->subject_id != $subject_id) continue;
                $schedule[$schedule_element->number][] = (int)$schedule_element->group->number;
            }
        if ($replaces)
            foreach ($replaces as $replace) {
                if (!in_array((int)$replace->group->number, $schedule[$replace->number]))
                    $schedule[$replace->number][] = (int)$replace->group->number;
            }
        ksort($schedule);
        $schedule_text = 'У кого завтра ' . $subject->name . '?' . PHP_EOL;
        foreach ($schedule as $number => $groups) {
            $schedule_text .= $number . ') ' . implode(', ', $groups) . PHP_EOL;
        }
        $schedule_text .= PHP_EOL . 'Данные предоставлены проектом @studyschedule (Расписание ККЭП)';

        $params = http_build_query([
            'owner_id' => $this->owner_id,
            'message' => $schedule_text,
            'from_group' => 1,
            'access_token' => $this->access_token
        ]);

        if (($result = file_get_contents('https://api.vk.com/method/wall.post?' . $params))) {
            var_dump(json_decode($result, true));
        } else {
            echo 'Error when send request...';
        }
        echo PHP_EOL;
    }

    public function actionAutopost()
    {
        $semester = Semesters::model()->actual();
        if (!$semester)
            throw new CException('Нет семестра');
        $tomorrow_date = (new DateTime())->add(new DateInterval("P1D"))
            ->format('Y-m-d');
        $tomorrow_time = strtotime($tomorrow_date);
        if (date('N', $tomorrow_time) == 7)
            throw new CException('Завтра выходной');
        $current_hour = date('G');
        $week_number = (date('W', $tomorrow_time) - date('W', strtotime($semester->start_date))) % ($semester->week_number + 1) + 1;
        $week_day = date('N', $tomorrow_time);
        /** @var GroupAutopost[] $autoposts */
        if (!($autoposts = GroupAutopost::model()->with(['group' => ['scopes' => 'filled', 'with' => ['schedule_elements' => ['condition' => 'week_number = :week_number AND week_day = :week_day', 'params' => [':week_number' => $week_number, ':week_day' => $week_day]]]]])->findAllByAttributes(['hour' => $current_hour, 'status' => GroupAutopost::STATUS_ACTIVE])))
            throw new CException('Нет групп для автопостинга');
        foreach ($autoposts as $autopost) {
            $replaces = CHtml::listData(GroupReplace::model()->with(['subject', 'classroom', 'teacher'])->findAllByAttributes(['group_id' => $autopost->group_id, 'date' => $tomorrow_date]), 'number', function ($model) {
                return array_merge($model->attributes, ['teacher' => $model->teacher, 'classroom' => $model->classroom, 'subject' => $model->subject]);
            });
            $schedule_elements = CHtml::listData($autopost->group->schedule_elements, 'number', function ($model) {
                return array_merge($model->attributes, ['teacher' => $model->teacher, 'classroom' => $model->classroom, 'subject' => $model->subject]);
            });
            $schedule = $replaces + $schedule_elements;
            ksort($schedule);
            $schedule_text = 'Расписание на завтра:' . PHP_EOL;
            $schedule_count = 0;
            /** @var $holiday Holiday */
            if (($holiday = Holiday::model()->findByAttributes(['date' => $tomorrow_date]))) {
                $schedule_text .= 'Выходной - ' . $holiday->name;
            } else {
                if ($schedule)
                    foreach ($schedule as $subject) {
                        if (isset($subject['cancel']) && $subject['cancel']) {
                            continue;
                        }
                        $schedule_count++;
                        $schedule_text .= $subject['number'] . ') ' . $subject['subject']->name;
                        if ($subject['teacher'])
                            $schedule_text .= ', ' . $subject['teacher']->lastname . ' ' . mb_substr($subject['teacher']->firstname, 0, 1, "UTF-8") . '.' . mb_substr($subject['teacher']->middlename, 0, 1, "UTF-8") . '.';
                        if ($subject['classroom'])
                            $schedule_text .= ' (' . $subject['classroom']->name . ')';
                        if (isset($subject['comment']) && strlen($subject['comment']))
                            $schedule_text .= ' - ' . $subject['comment'];
                        $schedule_text .= PHP_EOL;
                    }
                if (!$schedule || $schedule_count == 0)
                    $schedule_text .= 'Пар нет' . PHP_EOL;
            }

            $schedule_text .= 'Данные предоставлены проектом @studyschedule (Расписание ККЭП)';

            $params = http_build_query([
                'owner_id' => $autopost->page_id,
                'message' => $schedule_text,
                'from_group' => 1,
                'access_token' => $autopost->access_token
            ]);
            echo "----" . $autopost->group->number . "----" . PHP_EOL;
            $response = file_get_contents('https://api.vk.com/method/wall.post?' . $params);
            if ($response) {
                $answer = json_decode($response, true);
                var_dump($answer);
                if (isset($answer['error'])) {
                    $autopost->status = GroupAutopost::STATUS_DISABLE;
                    $autopost->save();
                    $email = $autopost->group->owner->email;
                    $mail = new YiiMailer("autopost", ['group' => $autopost->group]);
                    $mail->setFrom('marklangovoi@gmail.com', 'Система управления учебным расписанием');
                    $mail->setTo($email);
                    $mail->setSubject('Ошибка автопостинга в ВКонтакте');
                    $mail->send();
                }
            } else {
                echo 'Error when send request...';
            }
            echo PHP_EOL;
        }

    }
}
