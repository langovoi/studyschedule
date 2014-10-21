<?php

class IcsAnalyticsController extends Controller
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
        $dataProvider = new CActiveDataProvider('IcsAnalytics', [
            'sort' => [
                'defaultOrder' => 'time DESC, id DESC',
            ]
        ]);
        $model = new IcsAnalytics('search');
        if (!Yii::app()->request->isAjaxRequest || !Yii::app()->request->getParam('ajax'))
            $this->render('list', ['dataProvider' => $dataProvider, 'model' => $model]);
        else {
            $model->setAttributes(Yii::app()->request->getParam('IcsAnalytics'));
            $dataProvider = $model->search();
            $dataProvider->setSort([
                'defaultOrder' => 'time DESC, id DESC',
            ]);
            $this->renderPartial('_list', ['dataProvider' => $dataProvider, 'model' => $model]);
        }
    }

    public function actionChart()
    {
        $data = [];
        $temp_data = [];
        $criteria = new CDbCriteria();
        $criteria->select = 'COUNT(*) as count, time';
        $criteria->group = 'time';
        /** @var IcsAnalytics $ics_analytic_element */
        foreach (IcsAnalytics::model()->findAll($criteria) as $ics_analytic_element) {
            $time = strtotime(explode(' ', $ics_analytic_element->time)[0]) * 1000;
            if (isset($temp_data[$time]))
                $temp_data[$time] += (int)$ics_analytic_element->count;
            else $temp_data[$time] = (int)$ics_analytic_element->count;
        }
        foreach($temp_data as $date => $count)
            $data[] = [$date, $count];
        $this->render('chart', ['series' => [['name' => 'Количество запросов:', 'data' => $data]]]);
    }
}