<?php

class GroupsController extends Controller
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
        $dataProvider = new CActiveDataProvider('Group');
        $model = new Group('search');
        if (!Yii::app()->request->isAjaxRequest || !Yii::app()->request->getParam('ajax'))
            $this->render('list', ['dataProvider' => $dataProvider, 'model' => $model]);
        else {
            $model->setAttributes(Yii::app()->request->getParam('Group'));
            $dataProvider = $model->search();
            $this->renderPartial('_list', ['dataProvider' => $dataProvider, 'model' => $model]);
        }
    }

    public function actionUpdate($id)
    {
        $model = new Group();
        $users_list = CHtml::listData(Users::model()->findAll(), 'id', 'username');
        if ($model = $model->findByPk($id)) {
            if (Yii::app()->request->isPostRequest) {
                $model->setAttributes(Yii::app()->request->getParam('Group'));
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', 'Группа успешно сохранен');
                    $this->redirect(['index']);
                }
            }
            $this->render('form', ['model' => $model, 'users_list' => $users_list]);
        } else
            throw new CHttpException(404, 'Преподаватель не найден');
    }

    public function actionCreate()
    {
        $model = new Group('insert');
        $users_list = CHtml::listData(Users::model()->findAll(), 'id', 'username');
        if (Yii::app()->request->isPostRequest) {
            $group = Yii::app()->request->getParam('Group');
            $model->setAttributes($group);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Группа успешно создан');
                $this->redirect(['index']);
            }
        }
        $this->render('form', ['model' => $model, 'users_list' => $users_list]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        $model = new Group();
        if ($model = $model->findByPk($id)) {
            if ($confirm) {
                $model->delete();
                $this->redirect(['index']);
            } else {
                $this->render('delete', ['model' => $model]);
            }
        } else
            throw new CHttpException(404, 'Группа не найден');
    }
}