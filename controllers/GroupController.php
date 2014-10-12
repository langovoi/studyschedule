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
            'actionControl',
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
            $is_admin = Yii::app()->user->checkAccess('admin');
            $is_owner = $group->owner_id == Yii::app()->user->getId();
            $is_member = GroupMember::model()->findByAttributes(['group_id' => $group->id, 'user_id' => Yii::app()->user->getId()]);
            if (!$is_admin && !$is_owner && !$is_member)
                throw new CHttpException(403, 'У вас нет доступа к данной группе');
            self::$group = $group;
        } else
            throw new CHttpException(404);
        $filterChain->run();
    }

    public function filterActionControl($filterChain)
    {
        $is_admin = Yii::app()->user->checkAccess('admin');
        $is_owner = self::$group->owner_id == Yii::app()->user->getId();
        $allow_member = ['schedule', 'createscheduleelement', 'updatescheduleelement', 'deletescheduleelement'];
        if(!$is_owner && !$is_owner && in_array($this->action->getId(), $allow_member) == false)
            throw new CHttpException(403, 'Нет доступа');

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
        $this->render('schedule/form', ['model' => $model, 'classrooms' => $classrooms, 'teachers' => $teachers, 'subjects' => $subjects, 'numbers' => $numbers]);
    }

    public function actionUpdateScheduleElement($element_id)
    {
        $model = ScheduleElement::model()->findByPk($element_id);
        $semester = Semesters::model()->byStartDate()->find();
        if (!$model || $model->semester_id != $semester->id)
            throw new CHttpException(404, 'Элемент не найден');
        if (Yii::app()->request->isPostRequest) {
            $schedule_element = Yii::app()->request->getParam('ScheduleElement');
            $week_number = $model->week_number;
            $week_day = $model->week_day;
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
        if (($elements = ScheduleElement::model()->findAllByAttributes(['group_id' => self::$group->id, 'week_number' => $model->week_number, 'week_day' => $model->week_day, 'semester_id' => $semester->id])))
            foreach ($elements as $element) {
                if ($element->id !== $model->id && ($key = array_search($element->number, $numbers)) !== false)
                    unset($numbers[$key]);
            }
        $this->render('schedule/form', ['model' => $model, 'classrooms' => $classrooms, 'teachers' => $teachers, 'subjects' => $subjects, 'numbers' => $numbers]);
    }

    public function actionDeleteScheduleElement($element_id, $confirm = 0)
    {
        $model = ScheduleElement::model()->with('teacher', 'classroom', 'subject')->findByPk($element_id);
        $semester = Semesters::model()->byStartDate()->find();
        if (!$model || $model->semester_id != $semester->id)
            throw new CHttpException(404, 'Элемент не найден');
        if ($confirm) {
            $model->delete();
            Yii::app()->user->setFlash('success', 'Элемент успешно удален');
            $this->redirect(['schedule', 'id' => self::$group->number]);
        } else {
            $this->render('schedule/delete', ['model' => $model]);
        }
    }

    public function actionModerators()
    {
        $members = GroupMember::model()->with('user')->findAllByAttributes(['group_id' => self::$group->id]);
        $invites = GroupInvite::model()->findAllByAttributes(['group_id' => self::$group->id]);

        $this->render('moderators', ['members' => $members, 'invites' => $invites, 'group' => self::$group]);
    }

    public function actionCreateInvite()
    {
        $model = new GroupInvite();
        if (Yii::app()->request->isPostRequest) {
            $invite = Yii::app()->request->getParam('GroupInvite');
            $model->setAttributes($invite);
            $model->setAttributes([
                'group_id' => self::$group->id,
                'status' => GroupInvite::INVITE_CREATE,
            ]);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Приглашение успешно создано');
                $this->redirect(['moderators', 'id' => self::$group->number]);
            }
        }
        $this->render('invite/form', ['model' => $model]);
    }

    public function actionDeleteInvite($invite_id, $confirm = 0)
    {
        $model = GroupInvite::model()->findByPk($invite_id);
        if (!$model || $model->status != 0)
            throw new CHttpException(404, 'Элемент не найден');
        if ($confirm) {
            $model->setAttribute('status', GroupInvite::INVITE_CANCELED);
            if ($model->save())
                Yii::app()->user->setFlash('success', 'Приглашение успешно отменено');
            else Yii::app()->user->setFlash('error', 'Ошибка отмены приглашения');
            $this->redirect(['moderators', 'id' => self::$group->number]);
        } else {
            $this->render('invite/delete', ['model' => $model]);
        }
    }

    public function actionDeleteModerator($member_id, $confirm = 0)
    {
        $model = GroupMember::model()->findByPk($member_id);
        if (!$model)
            throw new CHttpException(404, 'Элемент не найден');
        if ($confirm) {
            if ($model->delete())
                Yii::app()->user->setFlash('success', 'Модератор успешно удален');
            else Yii::app()->user->setFlash('error', 'Ошибка удаления модератора');
            $this->redirect(['moderators', 'id' => self::$group->number]);
        } else {
            $this->render('moderator/delete', ['model' => $model]);
        }
    }

}