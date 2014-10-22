<?php

class CallListsElementsController extends Controller
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

    public function actionIndex($list_id)
    {
        /** @var CallLists $call_list */
        if (!($call_list = CallLists::model()->findByPk($list_id)))
            throw new CHttpException(404, 'Список не найден');
        $dataProvider = new CActiveDataProvider('CallListsElements', [
            'sort' => [
                'defaultOrder' => 'start_time ASC, end_time ASC',
            ],
            'criteria' => [
                'condition' => 'call_list_id = :list_id',
                'params' => [':list_id' => $list_id]
            ]
        ]);
        $model = new CallListsElements('search');
        if (!Yii::app()->request->isAjaxRequest || !Yii::app()->request->getParam('ajax'))
            $this->render('list', ['dataProvider' => $dataProvider, 'model' => $model, 'call_list' => $call_list]);
        else {
            $model->setAttributes(Yii::app()->request->getParam('CallListsElements'));
            $dataProvider = $model->search();
            $dataProvider->setCriteria([
                'condition' => 'call_list_id = :list_id',
                'params' => [':list_id' => $list_id]
            ]);
            $dataProvider->setSort([
                'defaultOrder' => 'start_time ASC, end_time ASC',
            ]);
            $this->renderPartial('_list', ['dataProvider' => $dataProvider, 'model' => $model, 'call_list' => $call_list]);
        }
    }

    public function actionCreate($list_id)
    {
        /** @var CallLists $call_list */
        $call_list = CallLists::model()->findByPk($list_id);
        if (!$call_list)
            throw new CHttpException(404, 'Список не найден');
        $model = new CallListsElements('insert');
        if (Yii::app()->request->isPostRequest) {
            $call_list_element = Yii::app()->request->getParam('CallListsElements');
            $model->setAttributes($call_list_element);
            $model->setAttribute('call_list_id', $call_list->id);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Элемент списка успешно создан');
                $this->redirect(['index', 'list_id' => $call_list->id]);
            }
        }
        $this->render('form', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        /** @var CallListsElements $model */
        $model = CallListsElements::model()->findByPk($id);
        if (!$model)
            throw new CHttpException(404, 'Элемент списка звонков не найден');
        if (Yii::app()->request->isPostRequest) {
            $call_list_id = $model->call_list_id;
            $call_list_element = Yii::app()->request->getParam('CallListsElements');
            $model->setAttributes($call_list_element);
            $model->setAttribute('call_list_id', $call_list_id);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Элемент списка успешно сохранен');
                $this->redirect(['index', 'list_id' => $call_list_id]);
            }
        }
        $this->render('form', ['model' => $model]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        $model = CallListsElements::model()->findByPk($id);
        if (!$model)
            throw new CHttpException(404, 'Элемент списока звонков не найден');
        if ($confirm) {
            $call_list_id = $model->call_list_id;
            if ($model->delete()) {
                $this->redirect(['index', 'list_id' => $call_list_id]);
            } else {
                Yii::app()->user->setFlash('error', 'Элемент списка звонков не удален');
            }
        } else {
            $this->render('delete', ['model' => $model]);
        }
    }
}