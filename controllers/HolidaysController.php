<?php

class HolidaysController extends Controller
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
        $holidays = new Holiday();
        $semester = new Semesters();
        /** @var Semesters $semester */
        $semester = $semester->byStartDate()->find();
        if ($semester)
            $holidays = $holidays->findAllByAttributes([], 'date >= :start_date AND date <= :end_date', [':start_date' => $semester->start_date, ':end_date' => $semester->end_date]);
        else
            $holidays = [];
        $model = new Holiday;
        $this->render('list', ['holidays' => $holidays, 'model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = new Holiday();
        $semester = Semesters::model()->byStartDate()->find();
        if (($model = $model->findByPk($id)) && strtotime($model->date) >= time()) {
            if (Yii::app()->request->isPostRequest) {
                $holiday = Yii::app()->request->getParam('Holiday');
                $model->setAttributes($holiday);
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', 'Выходной успешно сохранен');
                    $this->redirect(['index']);
                }
            }
            $this->render('form', ['model' => $model, 'semester' => $semester]);
        } else
            throw new CHttpException(404, 'Выходной не найден');
    }

    public function actionCreate()
    {
        $model = new Holiday('insert');
        $semester = Semesters::model()->byStartDate()->find();
        if (Yii::app()->request->isPostRequest) {
            $holiday = Yii::app()->request->getParam('Holiday');
            $model->setAttributes($holiday);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Выходной успешно создан');
                $this->redirect(['index']);
            }
        }
        $this->render('form', ['model' => $model, 'semester' => $semester]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        $model = new Holiday();
        if (($model = $model->findByPk($id)) && strtotime($model->date) >= time()) {
            if ($confirm) {
                $model->delete();
                $this->redirect(['index']);
            } else {
                $this->render('delete', ['model' => $model]);
            }
        } else
            throw new CHttpException(404, 'Выходной не найден');
    }
}