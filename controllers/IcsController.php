<?php

use Jsvrcek\ICS\Model\Calendar;
use Jsvrcek\ICS\Model\CalendarEvent;
use Jsvrcek\ICS\CalendarExport;
use Jsvrcek\ICS\CalendarStream;
use Jsvrcek\ICS\Utility\Formatter;

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
        $group = new Group();
        /** @var Group $group */
        if ($group = $group->findByAttributes(['number' => $id])) {
            $semester = new Semesters();
            /** @var Semesters $semester */
            if ($semester = $semester->byStartDate()->find()) {
                $schedule_elements = new ScheduleElement();
                if ($schedule_elements->count('group_id = :group_id AND semester_id = :semester_id', [':group_id' => $group->id, ':semester_id' => $semester->id])) {
                    $calendar = new Calendar();
                    $calendar->setProdId('-//Sc0Rp1D//KKEP//RU');
                    $calendar->setTimezone(new DateTimeZone('Europe/Moscow'));
                    $count_days = 24;
                    $start_date = time() - 60 * 60 * 24 * ($count_days / 2);
                    $end_date = time() + 60 * 60 * 24 * ($count_days / 2);
                    for ($i = $start_date; $i <= $end_date; $i = $i + 60 * 60 * 24) {
                        $week_number = (date('W', $i) - date('W', strtotime($semester->start_date))) % ($semester->week_number + 1) + 1;
                        if(date('N', $i) == 7) continue;
                        var_dump($week_number);
                        var_dump(date('d.m.Y', $i));
                    }
                    //var_dump($current_week);
                    die;
                    /*foreach ($schedule_elements as $schedule_element) {
                        $event = new CalendarEvent();
                    }*/

                    $calendarExport = new CalendarExport(new CalendarStream, new Formatter());
                    $calendarExport->addCalendar($calendar);

                    echo $calendarExport->getStream();
                } else
                    throw new CHttpException(404, 'Расписание для группы не найдена');
            } else
                throw new CHttpException(404, 'Нет семестров');
        } else
            throw new CHttpException(404, 'Группа не найдена');
    }
}