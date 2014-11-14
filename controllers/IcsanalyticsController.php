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
        if (!Yii::app()->request->isAjaxRequest && !Yii::app()->request->getParam('ajax'))
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
        switch ($interval) {
            default:
            case 'day':
                $time = "DATE_FORMAT(`time`, '%Y-%m-%d')";
                break;
            case 'hour':
                $time = "DATE_FORMAT(`time`, '%Y-%m-%d %H:00:00')";
                break;
        }
        $series = [];
        switch ($group) {
            case 'groups':
                $data = [];
                foreach (Yii::app()->db->createCommand()
                             ->select($time . " as 'date', count(*) as `count`, `group`")
                             ->from(IcsAnalytics::model()->tableName())
                             ->group($time . ', `group`')
                             ->queryAll() as $row)
                    $data[(int)$row['group']][] = [strtotime($row['date']) * 1000, (int)$row['count']];
                foreach ($data as $chart_name => $chart_data) {
                    $series[] = ['name' => $chart_name, 'data' => $chart_data];
                }
                break;
            case 'platforms':
                $data_temp = [];
                $data_temp['Android'] = [];
                foreach (Yii::app()->db->createCommand()
                             ->select($time . " as 'date', count(*) as `count`, `group`")
                             ->from(IcsAnalytics::model()->tableName())
                             ->where("`useragent` LIKE '%Android%'")
                             ->group($time)
                             ->queryAll() as $row)
                    $data_temp['Android'][] = [strtotime($row['date']) * 1000, (int)$row['count']];

                $data_temp['iOS/Mac'] = [];
                foreach (Yii::app()->db->createCommand()
                             ->select($time . " as 'date', count(*) as `count`, `group`")
                             ->from(IcsAnalytics::model()->tableName())
                             ->where("`useragent` LIKE '%iOS%' OR `useragent` LIKE '%Mac%'")
                             ->group($time)
                             ->queryAll() as $row)
                    $data_temp['iOS/Mac'][] = [strtotime($row['date']) * 1000, (int)$row['count']];

                $data_temp['Другие'] = [];
                foreach (Yii::app()->db->createCommand()
                             ->select($time . " as 'date', count(*) as `count`, `group`")
                             ->from(IcsAnalytics::model()->tableName())
                             ->where("`useragent` NOT LIKE '%iOS%' AND `useragent` NOT LIKE '%Mac%' AND `useragent` NOT LIKE '%Android%'")
                             ->group($time)
                             ->queryAll() as $row)
                    $data_temp['Другие'][] = [strtotime($row['date']) * 1000, (int)$row['count']];

                foreach ($data_temp as $chart_name => $data) {
                    $series[] = ['name' => $chart_name, 'data' => $data];
                }
                break;
            case 'all':
            default:
                $data = [];
                foreach (Yii::app()->db->createCommand()
                             ->select($time . " as 'date', count(*) as `count`")
                             ->from(IcsAnalytics::model()->tableName())
                             ->group($time)
                             ->queryAll() as $row)
                    $data[] = [strtotime($row['date']) * 1000, (int)$row['count']];
                $series[] = ['name' => 'Всего', 'data' => $data];
        }
        $this->render('chart', ['series' => $series, 'interval' => $interval, 'group' => $group]);
    }
}