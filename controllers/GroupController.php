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
            if (Yii::app()->user->checkAccess('admin') == false && $group->owner_id != Yii::app()->user->getId())
                throw new CHttpException(403, 'У вас нет доступа к данной группе');
            self::$group = $group;
        } else
            throw new CHttpException(404);
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
        $semester = Semesters::model()->byStartDate()->find();
        for ($i = 1; $i <= 2; $i++) {
            for ($j = 1; $j <= 6; $j++) {
                $criteria = new CDbCriteria();
                $criteria->addColumnCondition(['group_id' => self::$group->id, 'week_number' => $i, 'week_day' => $j, 'semester_id' => $semester->id]);
                $criteria->order = 'number';
                $schedule[$i][$j] = $schedule_model->with(['teacher', 'subject', 'classroom'])->findAll($criteria);
            }
        }
        $this->render('schedule', ['group' => self::$group, 'schedule' => $schedule]);
    }

    public function actionCreateScheduleElement($week_number, $week_day)
    {
        $model = new ScheduleElement();
        $semester = Semesters::model()->byStartDate()->find();
        if (Yii::app()->request->isPostRequest) {
            $schedule_element = Yii::app()->request->getParam('ScheduleElement');
            $model->setAttributes($schedule_element);
            $model->setAttributes([
                'week_number' => $week_number,
                'week_day' => $week_day,
                'group_id' => self::$group->id,
                'semester_id' => $semester->id
            ]);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Элемент успешно создан');
                $this->redirect(['schedule', 'id' => self::$group->number]);
            }
        }
        $classrooms = ['' => '-'];
        $subjects = ['' => '-'];
        $teachers = ['' => '-'];
        foreach (Classrooms::model()->findAll() as $classroom) {
            $classrooms[$classroom->id] = $classroom->name;
        }
        foreach (Subjects::model()->findAll() as $subject) {
            $subjects[$subject->id] = $subject->name;
        }
        foreach (Teachers::model()->findAll() as $teacher) {
            $teachers[$teacher->id] = join(' ', [$teacher->lastname, $teacher->firstname, $teacher->middlename]);
        }
        $numbers = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5];
        if (($elements = ScheduleElement::model()->findAllByAttributes(['group_id' => self::$group->id, 'week_number' => $week_number, 'week_day' => $week_day, 'semester_id' => $semester->id])))
            foreach ($elements as $element) {
                if (($key = array_search($element->number, $numbers)) !== false)
                    unset($numbers[$key]);
            }

        /*$schedule = [];
        $schedule_model = new ScheduleElement();
        for($i = 1; $i <= 2; $i++) {
            for($j = 1; $j <= 6; $j++) {
                $criteria = new CDbCriteria();
                $criteria->addColumnCondition(['group_id' => self::$group->id, 'week_number' => $i, 'week_day' => $j]);
                $criteria->order = 'number';
                $schedule[$i][$j] = $schedule_model->with(['teacher', 'subject', 'classroom'])->findAll($criteria);
            }
        }*/
        $this->render('schedule/form', ['model' => $model, 'classrooms' => $classrooms, 'teachers' => $teachers, 'subjects' => $subjects, 'numbers' => $numbers]);
    }
}