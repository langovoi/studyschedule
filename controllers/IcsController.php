<?php

use Jsvrcek\ICS\Model\Calendar;
use Jsvrcek\ICS\Model\CalendarEvent;
use Jsvrcek\ICS\CalendarExport;
use Jsvrcek\ICS\CalendarStream;
use Jsvrcek\ICS\Utility\Formatter;
use Jsvrcek\ICS\Model\Description\Location;

class IcsController extends Controller
{

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
        if (!isset($_SERVER['HTTP_USER_AGENT']))
            throw new CHttpException(403, 'У вас нет useragent, поэтому сюда вам нельзя');

        /** @var Semesters $semester */
        if (!($semester = Semesters::model()->with(['call_list', 'call_list_short'])->actual()))
            throw new CHttpException(404, 'Сейчас нет семестра :-(');

        // Аналитика
        if (!($unique_ics_id = Yii::app()->session->get('unique_ics_id', false))) {
            $unique_ics_id = uniqid();
            Yii::app()->session->add('unique_ics_id', $unique_ics_id);
        }

        $analytics = new IcsAnalytics();
        $analytics->setAttributes([
            'useragent' => $_SERVER['HTTP_USER_AGENT'],
            'group' => $id,
            'time' => date("Y-m-d H:i:s"),
            'ip' => get_client_ip(),
            'unique_id' => $unique_ics_id
        ]);
        $analytics->save();

        // Даты
        $count_days = 24;
        $current_date = date('Y-m-d');
        $period_interval = new DateInterval('P1D');
        $period_start = new DateTime($current_date);
        $period_start->modify('-' . floor($count_days / 2) . ' days');
        $period_end = new DateTime($current_date);
        $period_end->modify('+' . floor($count_days / 2) . ' days');
        $period = new DatePeriod($period_start, $period_interval, $period_end);
        $period_dates = [];
        $semester_start = new DateTime($semester->start_date);
        $semester_end = new DateTime($semester->end_date);
        /** @var DateTime $period_date */
        foreach ($period as $period_date) {
            $period_dates[] = $period_date->format('Y-m-d');
        }

        // Загрузка группы с расписанием и заменами за нужный период
        /** @var Group $group */
        if (!($group = Group::model()->filled()->with(['schedule_elements' => ['with' => ['teacher' => ['alias' => 's_teacher'], 'classroom' => ['alias' => 's_classroom'], 'subject' => ['alias' => 's_subject']]], 'replaces' => ['together' => false, 'with' => ['teacher' => ['alias' => 'r_teacher'], 'classroom' => ['alias' => 'r_classroom'], 'subject' => ['alias' => 'r_subject']], 'condition' => 'date >= :start_date AND date <= :end_date', 'params' => [':start_date' => $period_start->format('Y-m-d'), ':end_date' => $period_end->format('Y-m-d'),]]])->findByAttributes(['number' => $id]))) {
            throw new CHttpException(404, 'Группа не найдена или незаполнена');
        }
        // Преобразование массивов

        /** @var CallListsElements[] $call_list */
        $call_list = CHtml::listData($semester->call_list()->elements, 'number', function ($model) {
            return $model;
        });

        /** @var CallListsElements[] $call_list_short */
        $call_list_short = CHtml::listData($semester->call_list_short()->elements, 'number', function ($model) {
            return $model;
        });

        $schedule_elements = [];

        for ($i = 1; $i <= 2; $i++) {
            for ($j = 1; $j <= 6; $j++) {
                $schedule_elements[$i][$j] = CHtml::listData(array_filter($group->schedule_elements, function ($model) use (&$i, &$j) {
                    return $model->week_number == $i && $model->week_day == $j;
                }), 'number', function ($model) {
                    return $model;
                });
                ksort($schedule_elements[$i][$j], SORT_NUMERIC);
            }
            ksort($schedule_elements[$i], SORT_NUMERIC);
        }

        $replaces = [];

        foreach ($group->replaces as $replace) {
            $replaces[$replace->date][$replace->number] = $replace;
        }

        $calendar = new Calendar();
        $calendar->setProdId('-//Sc0Rp1D//KKEP//RU');
        $calendar->setTimezone(new DateTimeZone('Europe/Moscow'));
        $calendar->setCustomHeaders([
            'X-PUBLISHED-TTL' => 'PT1H',
            'REFRESH-INTERVAL' => 'VALUE=DURATION:PT1H',
        ]);
        $holidays = CHtml::listData(Holiday::model()->findAllByAttributes(['date' => $period_dates]), 'date', 'name');
        $short_days = CHtml::listData(ShortDay::model()->findAllByAttributes(['date' => $period_dates]), 'date', 'name');
        /** @var DateTime $period_element */
        foreach ($period as $period_element) {
            $week_day = $period_element->format('N');
            $date_formatted = $period_element->format('Y-m-d');
            if ($period_element < $semester_start || $week_day == 7 || array_key_exists($date_formatted, $holidays)) continue;
            if ($period_element > $semester_end) break;
            if ($week_day == 6 || array_key_exists($date_formatted, $short_days))
                $current_call_list = $call_list_short;
            else
                $current_call_list = $call_list;
            $week_number = (($semester->week_number + ($period_element->format('W') - $semester_start->format('W'))) % 2) ? 2 : 0;
            foreach (((isset($replaces[$date_formatted]) ? $replaces[$date_formatted] : []) + $schedule_elements[$week_number][$week_day]) as $schedule_element) {
                if (isset($schedule_element->cancel) && $schedule_element->cancel) continue;
                $event = new CalendarEvent();
                $start_time = clone $period_element;
                $call_list_start = explode(':', $current_call_list[$schedule_element->number]->start_time);
                $call_list_end = explode(':', $current_call_list[$schedule_element->number]->end_time);
                $start_time->setTime($call_list_start[0], $call_list_start[1]);
                $event->setStart($start_time);
                $end_time = clone $period_element;
                $end_time->setTime($call_list_end[0], $call_list_end[1]);
                $event->setEnd($end_time);
                $event->setSummary($schedule_element->subject->name);
                $event->setUid(md5($date_formatted . ' ' . $current_call_list[$schedule_element->number]->start_time));
                if ($schedule_element->classroom_id || $schedule_element->teacher_id) {
                    $location = new Location();
                    $desc = [];
                    if ($schedule_element->teacher_id)
                        $desc[] = $schedule_element->teacher->lastname . ' ' . mb_substr($schedule_element->teacher->firstname, 0, 1, "UTF-8") . '.' . mb_substr($schedule_element->teacher->middlename, 0, 1, "UTF-8") . '.';
                    if ($schedule_element->classroom_id)
                        $desc[] = $schedule_element->classroom->name;
                    $location->setName(implode(' | ', $desc));
                    $event->addLocation($location);
                }
                if ($schedule_element instanceof GroupReplace && strlen($schedule_element->comment))
                    $event->setDescription($schedule_element->comment);
                $calendar->addEvent($event);
            }
        }
        $calendarExport = new CalendarExport(new CalendarStream, new Formatter());
        $calendarExport->addCalendar($calendar);
        //header('Content-Type: text/calendar; charset=utf-8');
        echo $calendarExport->getStream();
    }
}

function get_client_ip()
{
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}