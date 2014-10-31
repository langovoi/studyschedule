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

    public function accessRules()
    {
        return [
            ['allow', 'roles' => ['admin', 'user']],
            ['deny', 'users' => ['*']],
        ];
    }

    public function filterGroupControl($filterChain)
    {
        if (!isset($_GET['id']))
            throw new CHttpException(404);
        /** @var Group $group */
        $group = Group::model()->findByAttributes(['number' => $_GET['id']]);
        if (!$group)
            throw new CHttpException(404, 'Данной группы не существует');
        $is_admin = Yii::app()->user->checkAccess('admin');
        $is_owner = $group->owner_id == Yii::app()->user->getId();
        $is_member = GroupMember::model()->findByAttributes(['group_id' => $group->id, 'user_id' => Yii::app()->user->getId()]);
        if (!$is_admin && !$is_owner && !$is_member)
            throw new CHttpException(403, 'У вас нет доступа к данной группе');
        self::$group = $group;

        $filterChain->run();
    }

    public function filterActionControl($filterChain)
    {
        $is_admin = Yii::app()->user->checkAccess('admin');
        $is_owner = self::$group->owner_id == Yii::app()->user->getId();
        $allow_member = ['schedule', 'createscheduleelement', 'updatescheduleelement', 'deletescheduleelement', 'replaces', 'createreplace', 'updatereplace', 'deletereplace'];
        if (!$is_admin && !$is_owner && in_array($this->action->getId(), $allow_member) == false)
            throw new CHttpException(403, 'Нет доступа');

        $filterChain->run();
    }

    public function actionSchedule()
    {
        $semester = Semesters::model()->actual();
        if (!$semester)
            throw new CHttpException(404, 'Сейчас нет семестра :-(');
        $schedule = [];
        $schedule_model = new ScheduleElement();
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
        $semester = Semesters::model()->actual();
        if (!$semester)
            throw new CHttpException(404, 'Сейчас нет семестра :-(');
        $model = new ScheduleElement();
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
        $classrooms = CHtml::listData(Classrooms::model()->byName()->findAll(), 'id', 'name');
        $subjects = CHtml::listData(Subjects::model()->byName()->findAll(), 'id', 'name');
        $teachers = CHtml::listData(Teachers::model()->byLastName()->findAll(), 'id', function ($model) {
            return join(' ', [$model->lastname, $model->firstname, $model->middlename]);
        });
        $numbers = ScheduleElement::getFreeNumber(self::$group->id, $semester->id, $week_number, $week_day);
        $numbers = array_combine($numbers, $numbers);

        $this->render('schedule/form', ['model' => $model, 'classrooms' => $classrooms, 'teachers' => $teachers, 'subjects' => $subjects, 'numbers' => $numbers]);
    }

    public function actionUpdateScheduleElement($element_id)
    {
        $semester = Semesters::model()->actual();
        if (!$semester)
            throw new CHttpException(404, 'Сейчас нет семестра :-(');
        /** @var ScheduleElement $model */
        $model = ScheduleElement::model()->findByPk($element_id);
        $week_number = $model->week_number;
        $week_day = $model->week_day;
        if (!$model || $model->semester_id != $semester->id)
            throw new CHttpException(404, 'Элемент не найден');
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
        $classrooms = CHtml::listData(Classrooms::model()->byName()->findAll(), 'id', 'name');
        $subjects = CHtml::listData(Subjects::model()->byName()->findAll(), 'id', 'name');
        $teachers = CHtml::listData(Teachers::model()->byLastName()->findAll(), 'id', function ($model) {
            return join(' ', [$model->lastname, $model->firstname, $model->middlename]);
        });
        $numbers = ScheduleElement::getFreeNumber(self::$group->id, $semester->id, $week_number, $week_day, $model->number);
        $numbers = array_combine($numbers, $numbers);

        $this->render('schedule/form', ['model' => $model, 'classrooms' => $classrooms, 'teachers' => $teachers, 'subjects' => $subjects, 'numbers' => $numbers]);
    }

    public function actionDeleteScheduleElement($element_id, $confirm = 0)
    {
        $semester = Semesters::model()->actual();
        if (!$semester)
            throw new CHttpException(404, 'Сейчас нет семестра :-(');
        $model = ScheduleElement::model()->with('teacher', 'classroom', 'subject')->findByPk($element_id);
        if (!$model || $model->semester_id != $semester->id)
            throw new CHttpException(404, 'Элемент не найден');
        if ($confirm) {
            $model->delete();
            Yii::app()->user->setFlash('success', 'Элемент успешно удален');
            $this->redirect(['schedule', 'id' => self::$group->number]);
        }

        $this->render('schedule/delete', ['model' => $model]);
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
        if (!$model || $model->status != GroupInvite::INVITE_CREATE)
            throw new CHttpException(404, 'Элемент не найден');
        if ($confirm) {
            $model->setAttribute('status', GroupInvite::INVITE_CANCELED);
            if ($model->save())
                Yii::app()->user->setFlash('success', 'Приглашение успешно отменено');
            else
                Yii::app()->user->setFlash('error', 'Ошибка отмены приглашения');
            $this->redirect(['moderators', 'id' => self::$group->number]);
        }

        $this->render('invite/delete', ['model' => $model]);
    }

    public function actionDeleteModerator($member_id, $confirm = 0)
    {
        $model = GroupMember::model()->findByPk($member_id);
        if (!$model)
            throw new CHttpException(404, 'Элемент не найден');
        if ($confirm) {
            if ($model->delete())
                Yii::app()->user->setFlash('success', 'Модератор успешно удален');
            else
                Yii::app()->user->setFlash('error', 'Ошибка удаления модератора');
            $this->redirect(['moderators', 'id' => self::$group->number]);
        }

        $this->render('moderator/delete', ['model' => $model]);
    }

    public function actionReplaces()
    {
        /** @var Semesters $semester */
        $semester = Semesters::model()->actual();
        if (!$semester)
            throw new CHttpException(404, 'Сейчас нет семестра :-(');
        $replaces = GroupReplace::model()->byDate()->findAllByAttributes(['group_id' => self::$group->id], 'date >= :start_date AND date <= :end_date AND date >= :current_date', [':current_date' => date('Y-m-d'), ':start_date' => $semester->start_date, ':end_date' => $semester->end_date]);

        $this->render('replaces', ['replaces' => $replaces, 'group' => self::$group]);
    }

    public function actionCreateReplace()
    {
        $semester = Semesters::model()->actual();
        if (!$semester)
            throw new CHttpException(404, 'Сейчас нет семестра :-(');
        $model = new GroupReplace();
        $classrooms = CHtml::listData(Classrooms::model()->byName()->findAll(), 'id', 'name');
        $subjects = CHtml::listData(Subjects::model()->byName()->findAll(), 'id', 'name');
        $teachers = CHtml::listData(Teachers::model()->byLastName()->findAll(), 'id', function ($model) {
            return join(' ', [$model->lastname, $model->firstname, $model->middlename]);
        });
        if (Yii::app()->request->isPostRequest) {
            $replace = Yii::app()->request->getParam('GroupReplace');
            $model->setAttributes($replace);
            $model->setAttributes(['group_id' => self::$group->id]);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Замена успешна создана');
                $this->redirect(['replaces', 'id' => self::$group->number]);
            }
        }

        $this->render('replace/form', ['semester' => $semester, 'model' => $model, 'teachers' => $teachers, 'subjects' => $subjects, 'classrooms' => $classrooms, 'numbers' => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5]]);
    }

    public function actionUpdateReplace($replace_id)
    {
        $semester = Semesters::model()->actual();
        if (!$semester)
            throw new CHttpException(404, 'Сейчас нет семестра :-(');
        /** @var GroupReplace $model */
        $model = GroupReplace::model()->findByPk($replace_id);
        if (!$model || strtotime($model->date) < strtotime(date('Y-m-d')))
            throw new CHttpException(404, 'Замена не найдена');
        $classrooms = CHtml::listData(Classrooms::model()->byName()->findAll(), 'id', 'name');
        $subjects = CHtml::listData(Subjects::model()->byName()->findAll(), 'id', 'name');
        $teachers = CHtml::listData(Teachers::model()->byLastName()->findAll(), 'id', function ($model) {
            return join(' ', [$model->lastname, $model->firstname, $model->middlename]);
        });
        if (Yii::app()->request->isPostRequest) {
            $replace = Yii::app()->request->getParam('GroupReplace');
            $model->setAttributes($replace);
            $model->setAttributes(['group_id' => self::$group->id]);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Замена успешна обновлена');
                $this->redirect(['replaces', 'id' => self::$group->number]);
            }
        }

        $this->render('replace/form', ['semester' => $semester, 'model' => $model, 'teachers' => $teachers, 'subjects' => $subjects, 'classrooms' => $classrooms, 'numbers' => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5]]);
    }

    public function actionDeleteReplace($replace_id, $confirm = 0)
    {
        /** @var GroupReplace $model */
        $model = GroupReplace::model()->findByPk($replace_id);
        if (!$model || strtotime($model->date) < strtotime(date('Y-m-d')))
            throw new CHttpException(404, 'Элемент не найден');
        if ($confirm) {
            if ($model->delete())
                Yii::app()->user->setFlash('success', 'Замена успешна удален');
            else
                Yii::app()->user->setFlash('error', 'Ошибка удаления замены');
            $this->redirect(['replaces', 'id' => self::$group->number]);
        }

        $this->render('replace/delete', ['model' => $model]);
    }

    public function actionAutoPost($step = 0)
    {
        if (GroupAutopost::model()->findByAttributes(['group_id' => self::$group->id]))
            throw new CHttpException(403, 'У вашей группы уже есть автопостинг, его настройки будут доступны позже');
        if (!in_array($step, [0, 1, 2]))
            throw new CHttpException(404, 'Данный шаг отсутсвует');
        switch ($step) {
            case 0:
                $this->render('autopost/token', ['group' => self::$group]);
                break;
            case 1:
                $url = Yii::app()->request->getParam('access_token');
                if (!$url) {
                    Yii::app()->user->setFlash('error', 'Вы не вставили ссылку');
                    $this->render('autopost/token', ['group' => self::$group]);
                } else {
                    parse_str(explode('#', $url)[1], $url_data);
                    if (!isset($url_data['access_token'])) {
                        Yii::app()->user->setFlash('error', 'Ссылка неверна');
                        $this->render('autopost/token', ['group' => self::$group]);
                    } else {
                        $access_token = $url_data['access_token'];
                        $params = http_build_query([
                            'access_token' => $access_token
                        ]);
                        $answer = json_decode(file_get_contents('https://api.vk.com/method/account.getAppPermissions?' . $params), true);
                        if (isset($answer['error'])) {
                            Yii::app()->user->setFlash('error', 'Ключ в ссылке неверный');
                            $this->render('autopost/token', ['group' => self::$group]);
                        } else {
                            if (($answer['response'] & 262144) == 0 || ($answer['response'] & 8192) == 0 || ($answer['response'] & 65536) == 0) {
                                Yii::app()->user->setFlash('error', 'Ключ в ссылке не дает нужных прав');
                                $this->render('autopost/token', ['group' => self::$group]);
                            } else {
                                $params = http_build_query([
                                    'extended' => 1,
                                    'access_token' => $access_token,
                                    'filter' => 'moder'
                                ]);
                                $answer = json_decode(file_get_contents('https://api.vk.com/method/groups.get?' . $params), true);
                                Yii::app()->session->add('vk_access_token', $access_token);
                                $groups = [];
                                foreach ($answer['response'] as $group)
                                    if ($group['gid'])
                                        $groups[-(int)($group['gid'])] = $group['name'];
                                $this->render('autopost/page_and_hour', ['group' => self::$group, 'groups' => $groups, 'hours' => array_combine(array_values(GroupAutopost::$hours), array_values(GroupAutopost::$hours))]);
                            }
                        }
                    }
                }
                break;
            case 2:
                $page_id = Yii::app()->request->getParam('page_id');
                $hour = Yii::app()->request->getParam('hour');
                $access_token = Yii::app()->session->get('vk_access_token');
                if (!$access_token || !$page_id || !$hour || !in_array($hour, GroupAutopost::$hours)) {
                    $params = http_build_query([
                        'extended' => 1,
                        'access_token' => $access_token,
                        'filter' => 'moder'
                    ]);
                    $answer = json_decode(file_get_contents('https://api.vk.com/method/groups.get?' . $params), true);
                    if (isset($answer['error'])) {
                        Yii::app()->user->setFlash('error', 'Ключ неверный');
                        $this->render('autopost/token', ['group' => self::$group]);
                    } else {
                        Yii::app()->user->setFlash('error', 'Вы не указали час или группу');
                        Yii::app()->session->add('vk_access_token', $access_token);
                        $groups = [];
                        foreach ($answer['response'] as $group)
                            if ($group['gid'])
                                $groups[-(int)($group['gid'])] = $group['name'];
                        $this->render('autopost/page_and_hour', ['group' => self::$group, 'groups' => $groups, 'hours' => array_combine(array_values(GroupAutopost::$hours), array_values(GroupAutopost::$hours))]);
                    }
                } else {
                    Yii::app()->session->remove('access_token');
                    $autopost = new GroupAutopost();
                    $autopost->setAttributes([
                        'group_id' => self::$group->id,
                        'page_id' => $page_id,
                        'hour' => $hour,
                        'access_token' => $access_token
                    ]);
                    $autopost->save();
                    Yii::app()->user->setFlash('success', 'Автопостинг в ВК успешно настроен');
                    $this->redirect(['site/dashboard']);
                }
                break;
        }
    }

}