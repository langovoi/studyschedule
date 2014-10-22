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
        $semester = Semesters::model()->actual();
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
        $semester = Semesters::model()->actual();
        if (($model = $model->findByPk($id)) && strtotime($model->date) >= strtotime(date('Y-m-d'))) {
            if (Yii::app()->request->isPostRequest) {
                $holiday = Yii::app()->request->getParam('ShortDay');
                $model->setAttributes($holiday);
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', 'Сокращенный день успешно сохранен');
                    $this->redirect(['index']);
                }
            }
            $this->render('form', ['model' => $model, 'semester' => $semester]);
        } else
            throw new CHttpException(404, 'Сокращенный день не найден');
    }

    public function actionCreate()
    {
        $model = new ShortDay('insert');
        $semester = Semesters::model()->actual();
        if (Yii::app()->request->isPostRequest) {
            $holiday = Yii::app()->request->getParam('ShortDay');
            $model->setAttributes($holiday);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Сокращенный день успешно создан');
                $this->redirect(['index']);
            }
        }
        $this->render('form', ['model' => $model, 'semester' => $semester]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        $model = new ShortDay();
        if (($model = $model->findByPk($id)) && strtotime($model->date) >= strtotime(date('Y-m-d'))) {
            if ($confirm) {
                $model->delete();
                $this->redirect(['index']);
            } else {
                $this->render('delete', ['model' => $model]);
            }
        } else
            throw new CHttpException(404, 'Сокращенный день не найден');
    }
}