<?php

use Jsvrcek\ICS\Model\Calendar;
use Jsvrcek\ICS\Model\CalendarEvent;
use Jsvrcek\ICS\CalendarExport;
use Jsvrcek\ICS\CalendarStream;
use Jsvrcek\ICS\Utility\Formatter;
use Jsvrcek\ICS\Model\Description\Location;

class IcsController extends Controller
{
    private $schedule_elements = [];

    public function filters()
    {
        return [
            'accessControl',
        ];
    }

    public function accessRules()
    {
        return [
            ['allow', 'users' => ['*']],
        ];
    }

    public function actionGroup($id)
    {
        $group = new Group();
        /** @var Group $group */
        if (!($group = $group->findByAttributes(['number' => $id])))
            throw new CHttpException(404, 'Группа не найдена');
        $semester = Semesters::model()->byStartDate()->with(['call_list', 'call_list_short'])->find();
        /** @var Semesters $semester */
        if (!$semester)
            throw new CHttpException(404, 'Нет семестров');
        /** @var CallListElements[] $call_list */
        /** @var CallListElements[] $call_list_short */
        $call_list = [];
        $call_list_short = [];
        /** @var CallListsElements $element */
        foreach ($semester->call_list()->elements as $element) {
            $call_list[$element->number] = $element;
        }
        foreach ($semester->call_list_short()->elements as $element) {
            $call_list_short[$element->number] = $element;
        }
        if (ScheduleElement::model()->count('group_id = :group_id AND semester_id = :semester_id', [':group_id' => $group->id, ':semester_id' => $semester->id])) {
            date_default_timezone_set('Europe/Moscow');
            $calendar = new Calendar();
            $calendar->setProdId('-//Sc0Rp1D//KKEP//RU');
            $calendar->setTimezone(new DateTimeZone('Europe/Moscow'));
            $count_days = 24;
            $start_date = time() - 60 * 60 * 24 * ($count_days / 2);
            $end_date = time() + 60 * 60 * 24 * ($count_days / 2);
            for ($i = $start_date; $i <= $end_date; $i = $i + 60 * 60 * 24) {
                if (date('N', $i) == 7 || Holiday::model()->findByAttributes(['date' => date('Y-m-d', $i)])) continue;
                if (date('N', $i) == 6 || ShortDay::model()->findByAttributes(['date' => date('Y-m-d', $i)]))
                    $current_call_list = $call_list_short;
                else
                    $current_call_list = $call_list;
                $week_number = (date('W', $i) - date('W', strtotime($semester->start_date))) % ($semester->week_number + 1) + 1;
                $week_day = date('N', $i);
                $schedule_elements = $this->getScheduleElement($group->id, $semester->id, $week_number, $week_day);
                $numbers = [1, 2, 3, 4, 5];
                foreach ($schedule_elements as $schedule_element) {
                    unset($numbers[array_search($schedule_element->number, $numbers)]);
                    $schedule_element_temp = GroupReplace::model()->findByAttributes(['group_id' => $group->id, 'date' => date('Y-m-d', $i), 'number' => $schedule_element->number]);
                    if ($schedule_element_temp) {
                        if ($schedule_element_temp->cancel) continue;
                        $schedule_element = $schedule_element_temp;
                    }
                    $event = new CalendarEvent();
                    $start_time = new DateTime(date('Y-m-d', $i));
                    $call_list_start = explode(':', $current_call_list[$schedule_element->number]->start_time);
                    $call_list_end = explode(':', $current_call_list[$schedule_element->number]->end_time);
                    $start_time->setTime($call_list_start[0], $call_list_start[1])->setTimezone(new DateTimeZone('Europe/Moscow'));
                    $event->setStart($start_time);
                    $end_time = new DateTime(date('Y-m-d', $i));
                    $end_time->setTime($call_list_end[0], $call_list_end[1])->setTimezone(new DateTimeZone('Europe/Moscow'));
                    $event->setEnd($end_time);
                    $event->setSummary($schedule_element->subject->name);
                    $event->setUid(md5(date('d.m.Y', $i) . ' ' . $current_call_list[$schedule_element->number]->start_time));
                    if ($schedule_element->classroom_id || $schedule_element->teacher_id) {
                        $location = new Location();
                        $desc = [];
                        if ($schedule_element->teacher_id)
                            $desc[] = $schedule_element->teacher->lastname . ' ' . mb_substr($schedule_element->teacher->firstname, 0, 1, "UTF-8") . '.' . mb_substr($schedule_element->teacher->middlename, 0, 1, "UTF-8");
                        if ($schedule_element->classroom_id)
                            $desc[] = $schedule_element->classroom->name;
                        $location->setName(implode(' | ', $desc));
                        $event->addLocation($location);
                    }
                    $calendar->addEvent($event);
                }
                $replaces = GroupReplace::model()->findAllByAttributes(['group_id' => $group->id, 'date' => date('Y-m-d', $i)], 'number IN (:numbers)', [':numbers' => implode(',', $numbers)]);
                if ($replaces)
                    foreach ($replaces as $schedule_element) {
                        if ($schedule_element->cancel) continue;
                        $event = new CalendarEvent();
                        $start_time = new DateTime(date('Y-m-d', $i));
                        $call_list_start = explode(':', $current_call_list[$schedule_element->number]->start_time);
                        $call_list_end = explode(':', $current_call_list[$schedule_element->number]->end_time);
                        $start_time->setTime($call_list_start[0], $call_list_start[1])->setTimezone(new DateTimeZone('Europe/Moscow'));
                        $event->setStart($start_time);
                        $end_time = new DateTime(date('Y-m-d', $i));
                        $end_time->setTime($call_list_end[0], $call_list_end[1])->setTimezone(new DateTimeZone('Europe/Moscow'));
                        $event->setEnd($end_time);
                        $event->setSummary($schedule_element->subject->name);
                        $event->setUid(md5(date('d.m.Y', $i) . ' ' . $current_call_list[$schedule_element->number]->start_time));
                        if ($schedule_element->classroom_id || $schedule_element->teacher_id) {
                            $location = new Location();
                            $desc = [];
                            if ($schedule_element->teacher_id)
                                $desc[] = $schedule_element->teacher->lastname . ' ' . mb_substr($schedule_element->teacher->firstname, 0, 1, "UTF-8") . '.' . mb_substr($schedule_element->teacher->middlename, 0, 1, "UTF-8");
                            if ($schedule_element->classroom_id)
                                $desc[] = $schedule_element->classroom->name;
                            $location->setName(implode(' | ', $desc));
                            $event->addLocation($location);
                        }
                        $calendar->addEvent($event);
                    }
            }
            $calendarExport = new CalendarExport(new CalendarStream, new Formatter());
            $calendarExport->addCalendar($calendar);

            echo $calendarExport->getStream();
        } else
            throw new CHttpException(404, 'Расписание для группы не найдена');
    }

    private function getScheduleElement($group_id, $semester_id, $week_number, $week_day)
    {
        if (!isset($this->schedule_elements[$week_number . "_" . $week_day]))
            $this->schedule_elements[$week_number . "_" . $week_day] = ScheduleElement::model()->byNumber()->findAllByAttributes(['group_id' => $group_id, 'semester_id' => $semester_id, 'week_number' => $week_number, 'week_day' => $week_day]);
        return $this->schedule_elements[$week_number . "_" . $week_day];
    }
}