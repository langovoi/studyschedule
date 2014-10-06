<?php

class ShortDaysController extends Controller
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
            ['allow', 'roles' => ['admin']],
            ['deny', 'users' => ['*']],
        ];
    }

    public function actionIndex()
    {
        $short_days = new ShortDay();
        $semester = new Semesters();
        /** @var Semesters $semester */
        $semester = $semester->byStartDate()->find();
        if ($semester)
            $short_days = $short_days->findAllByAttributes([], 'date >= :start_date AND date <= :end_date', [':start_date' => $semester->start_date, ':end_date' => $semester->end_date]);
        else
            $short_days = [];
        $model = new ShortDay;
        $this->render('list', ['short_days' => $short_days, 'model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = new ShortDay();
        if (($model = $model->findByPk($id)) && strtotime($model->date) >= time()) {
            if (Yii::app()->request->isPostRequest) {
                $holiday = Yii::app()->request->getParam('ShortDay');
                $model->setAttributes($holiday);
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', 'Скоращенный день успешно сохранен');
                    $this->redirect(['index']);
                }
            }
            $this->render('form', ['model' => $model]);
        } else
            throw new CHttpException(404, 'Скоращенный день не найден');
    }

    public function actionCreate()
    {
        $model = new ShortDay('insert');

        if (Yii::app()->request->isPostRequest) {
            $holiday = Yii::app()->request->getParam('ShortDay');
            $model->setAttributes($holiday);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Скоращенный день успешно создан');
                $this->redirect(['index']);
            }
        }
        $this->render('form', ['model' => $model]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        $model = new ShortDay();
        if (($model = $model->findByPk($id)) && strtotime($model->date) >= time()) {
            if ($confirm) {
                $model->delete();
                $this->redirect(['index']);
            } else {
                $this->render('delete', ['model' => $model]);
            }
        } else
            throw new CHttpException(404, 'Скоращенный день не найден');
    }
}