<?php

class GroupController extends Controller
{
    /** @var bool|Group */
    static private $group = false;

    public function filters()
    {
        return [
            'accessControl',
            'groupControl',
        ];
    }

    public function filterGroupControl($filterChain)
    {
        if (isset($_GET['id'])) {
            $group = new Group();
            $group = $group->findByAttributes(['number' => $_GET['id']]);
            /** @var Group $group */
            if (!$group)
                throw new CHttpException(404, 'Данной группы не существует');
            if ($group->owner_id != Yii::app()->user->getId())
                throw new CHttpException(403, 'У вас нет доступа к данной группе');
            self::$group = $group;
        }
        $filterChain->run();
    }

    public function accessRules()
    {
        return [
            ['allow', 'roles' => ['admin', 'user']],
            ['deny', 'users' => ['*']],
        ];
    }

    public function actionSchedule()
    {
        $schedule = [];
        $schedule_model = new ScheduleElement();
        for($i = 1; $i <= 2; $i++) {
            for($j = 1; $j <= 6; $j++) {
                $criteria = new CDbCriteria();
                $criteria->addColumnCondition(['group_id' => self::$group->id, 'week_number' => $i, 'week_day' => $j]);
                $criteria->order = 'number';
                $schedule[$i][$j] = $schedule_model->with(['teacher', 'subject', 'classroom'])->findAll($criteria);
            }
        }
        $this->render('schedule', ['group' => self::$group, 'schedule' => $schedule]);
    }
}