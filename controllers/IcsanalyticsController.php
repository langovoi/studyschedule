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

    public function actionChart($interval = 'day', $group = 'all')
    {
        if (!in_array($interval, ['day', 'hour']))
            throw new CHttpException(404);
        if (!in_array($group, ['all', 'groups', 'platforms']))
            throw new CHttpException(404);
        $series = [];
        switch ($group) {
            case 'groups':
                $data_temp = [];
                /** @var IcsAnalytics $element */
                foreach (IcsAnalytics::model()->findAll() as $element) {
                    $data_temp[$element->group][] = $element;
                }
                foreach ($data_temp as $group => $data) {
                    $series[] = ['name' => $group, 'data' => $this->dataCountByInterval($data, $interval)];
                }
                break;
            case 'platforms':
                $data_temp = [];
                $criteria = new CDbCriteria();
                $criteria->addSearchCondition('useragent', 'Android');
                $data_temp['Android'] = IcsAnalytics::model()->findAll($criteria);
                $criteria = new CDbCriteria();
                $criteria->addSearchCondition('useragent', 'iOS');
                $criteria->addSearchCondition('useragent', 'Mac');
                $data_temp['iOS/Mac'] = IcsAnalytics::model()->findAll($criteria);
                $criteria = new CDbCriteria();
                $criteria->addSearchCondition('useragent', 'Android', true, 'AND', 'NOT LIKE');
                $criteria->addSearchCondition('useragent', 'iOS', true, 'AND', 'NOT LIKE');
                $data_temp['Другие'] = IcsAnalytics::model()->findAll($criteria);
                foreach ($data_temp as $group => $data) {
                    $series[] = ['name' => $group, 'data' => $this->dataCountByInterval($data, $interval)];
                }
                break;
            case 'all':
            default:
                $series[] = ['name' => 'Всего', 'data' => $this->dataCountByInterval(IcsAnalytics::model()->findAll(), $interval)];
        }
        $this->render('chart', ['series' => $series, 'interval' => $interval, 'group' => $group]);
    }

    private function dataCountByInterval($data, $interval)
    {
        if (!in_array($interval, ['day', 'hour']))
            return false;
        $data_temp = [];
        $return = [];
        /** @var IcsAnalytics $data_element */
        foreach ($data as $data_element) {
            $datetime = explode(' ', $data_element->time);
            $time = explode(':', $datetime[1]);
            switch ($interval) {
                case 'hour':
                    $timestamp = strtotime($datetime[0] . ' ' . $time[0] . ':00:00');
                    if (!isset($data_temp[$timestamp]))
                        $data_temp[$timestamp] = 1;
                    else $data_temp[$timestamp]++;
                    break;
                case 'day':
                default:
                    $timestamp = strtotime($datetime[0] . ' 00:00:00');
                    if (!isset($data_temp[$timestamp]))
                        $data_temp[$timestamp] = 1;
                    else $data_temp[$timestamp]++;
            }
        }
        foreach ($data_temp as $time => $count) {
            $return[] = [(int)($time * 1000), (int)$count];
        }
        return $return;
    }
}