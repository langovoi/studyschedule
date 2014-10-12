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
        $call_list = new CallLists();
        $call_list_elements = [];
        /** @var CallLists $call_list */
        if ($call_list = $call_list->findByPk($list_id)) {
            $call_list_elements = $call_list->elements;
            $model = new CallListsElements();
        } else
            throw new CHttpException(404, 'Список не найден');
        $this->render('list', ['call_list_elements' => $call_list_elements, 'model' => $model, 'call_list' => $call_list]);
    }

    public function actionCreate($list_id)
    {
        $call_list = new CallLists();
        /** @var CallLists $call_list */
        if ($call_list = $call_list->findByPk($list_id)) {
            $model = new CallListsElements('insert');
            if (Yii::app()->request->isPostRequest) {
                $call_list_element = Yii::app()->request->getParam('CallListsElements');
                $model->setAttributes($call_list_element);
                $model->call_list_id = $call_list->id;
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::app()->user->setFlash('success', 'Элемент списка успешно создан');
                        $this->redirect(['index', 'list_id' => $call_list->id]);
                    } else {
                        Yii::app()->user->setFlash('error', 'Ошибка при сохранении');
                        var_dump($model);
                    }
                }
            }
        } else
            throw new CHttpException(404, 'Список не найден');
        $this->render('form', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = new CallListsElements();
        /** @var CallListsElements $model */
        if ($model = $model->findByPk($id)) {
            $call_list_id = $model->call_list_id;
            if (Yii::app()->request->isPostRequest) {
                $call_list_element = Yii::app()->request->getParam('CallListsElements');
                $model->setAttributes($call_list_element);
                $model->call_list_id = $call_list_id;
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', 'Элемент списка успешно сохранен');
                    $this->redirect(['index', 'list_id' => $call_list_id]);
                } else {
                    Yii::app()->user->setFlash('error', 'Ошибка при сохранении');
                    var_dump($model);
                }
            }
            $this->render('form', ['model' => $model]);
        } else
            throw new CHttpException(404, 'Элемент списка звонков не найден');
    }

    public function actionDelete($id, $confirm = 0)
    {
        $model = new CallListsElements();
        if ($model = $model->findByPk($id)) {
            $call_list_id = $model->call_list_id;
            if ($confirm) {
                if ($model->delete()) {
                    $this->redirect(['index', 'list_id' => $call_list_id]);
                } else {
                    Yii::app()->user->setFlash('error', 'Элемент списка звонков не удален');
                }
            } else {
                $this->render('delete', ['model' => $model]);
            }
        } else
            throw new CHttpException(404, 'Элемент списока звонков не найден');
    }
}