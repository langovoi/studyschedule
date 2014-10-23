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
        $week_number = (date('W', $tomorrow_time) - date('W', strtotime($semester->start_date))) % ($semester->week_number + 1) + 1;
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
        asort($schedule);
        $schedule_text = 'У кого завтра физра?' . PHP_EOL;
        foreach ($schedule as $number => $groups) {
            $schedule_text .= $number . ') ' . implode(', ', $groups) . PHP_EOL;
        }

        $params = http_build_query([
            'owner_id' => $this->owner_id,
            'message' => $schedule_text,
            'from_group' => 1,
            'access_token' => $this->access_token
        ]);

        file_get_contents('https://api.vk.com/method/wall.post?' . $params);
    }
}