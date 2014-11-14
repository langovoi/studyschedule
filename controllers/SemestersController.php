<?php

class SemestersController extends Controller
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
        $dataProvider = new CActiveDataProvider('Semesters');
        $model = new Semesters('search');
        if (!Yii::app()->request->isAjaxRequest && !Yii::app()->request->getParam('ajax'))
            $this->render('list', ['dataProvider' => $dataProvider, 'model' => $model]);
        else {
            $model->setAttributes(Yii::app()->request->getParam('Semesters'));
            $dataProvider = $model->search();
            $this->renderPartial('_list', ['dataProvider' => $dataProvider, 'model' => $model]);
        }
    }

    public function actionUpdate($id)
    {
        $model = new Semesters();
        $call_lists = CHtml::listData(CallLists::model()->findAll(), 'id', 'name');
        if ($model = $model->findByPk($id)) {
            if (Yii::app()->request->isPostRequest) {
                $semester = Yii::app()->request->getParam('Semesters');
                $model->setAttributes($semester);
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', 'Семестр успешно сохранен');
                    $this->redirect(['index']);
                }
            }
            $this->render('form', ['model' => $model, 'call_lists' => $call_lists]);
        } else
            throw new CHttpException(404, 'Семестр не найден');
    }

    public function actionCreate()
    {
        $model = new Semesters('insert');
        $call_lists = CHtml::listData(CallLists::model()->findAll(), 'id', 'name');
        if (Yii::app()->request->isPostRequest) {
            $semester = Yii::app()->request->getParam('Semesters');
            $model->setAttributes($semester);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Семестр успешно создан');
                $this->redirect(['index']);
            }
        }
        $this->render('form', ['model' => $model, 'call_lists' => $call_lists]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        $model = new Semesters();
        if ($model = $model->findByPk($id)) {
            if ($confirm) {
                $model->delete();
                $this->redirect(['index']);
            } else {
                $this->render('delete', ['model' => $model]);
            }
        } else
            throw new CHttpException(404, 'Семестр не найден');
    }
}