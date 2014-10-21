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

    public function actionChart($type = 'day', $by_groups = false)
    {
        if (!in_array($type, ['day', 'hour']))
            throw new CHttpException(404);
        $data = [];
        $temp_data = [];
        $criteria = new CDbCriteria();
        $criteria->select = ['COUNT(*) as count', 'time', '`group`'];
        $criteria->group = 'time';
        /** @var IcsAnalytics $ics_analytic_element */
        foreach (IcsAnalytics::model()->findAll($criteria) as $ics_analytic_element) {
            $exploded = explode(' ', $ics_analytic_element->time);
            $date = $exploded[0];
            if ($type == 'hour') {
                $time = explode(':', $exploded[1])[0];
            } else $time = '00';
            $time = strtotime($date . ' ' . $time . ':00:00') * 1000;
            if (!$by_groups) {
                if (isset($temp_data[$time]))
                    $temp_data[$time]++;
                else $temp_data[$time] = 1;
            } else {
                if (isset($temp_data[$ics_analytic_element->group][$time]))
                    $temp_data[$ics_analytic_element->group][$time]++;
                else $temp_data[$ics_analytic_element->group][$time] = 1;
            }
        }
        if (!$by_groups)
            foreach ($temp_data as $date => $count)
                $data[] = [$date, $count];
        else
            foreach ($temp_data as $group => $values) {
                $group_data = ['name' => (string)$group, 'data' => []];
                foreach ($values as $date => $count)
                    $group_data['data'][] = [$date, $count];
                $data[] = $group_data;
            }
        $this->render('chart', ['series' => ($by_groups == false ? [['name' => 'Количество запросов:', 'data' => $data]] : $data)]);
    }
}