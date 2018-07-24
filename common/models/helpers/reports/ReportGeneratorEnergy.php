<?php

namespace common\models\helpers\reports;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

use common\widgets\Alert;
use common\models\Site;
use common\models\SiteMeterTree;
use common\models\Tenant;
use common\models\RuleSingleChannel;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\ElectricityMeterRawData;
use common\components\i18n\Formatter;

class ReportGeneratorEnergy extends ReportGenerator implements IReportGenerator
{
	public static $tree = [];


    /**
     * Generate report
     *
     * @param string|integer $report_from_date
     * @param string|integer $report_to_date
     * @param Site $site
     * @param array $tenants
     * @param array $params
     * @return array
     */
    public static function generate($report_from_date, $report_to_date, $site, $tenants = [], array $params = []) {
		$site_owner = $site->relationUser;
		$vat_included = $site->getIncludeVat();
		$from_date = TimeManipulator::getStartOfDay($report_from_date);
		$to_date = TimeManipulator::getEndOfDay($report_to_date);
		
		$data = [];
		$data['site'] = $site;
		$data['site_owner'] = $site_owner;

		$meter_tree = MeterChannel::find()
		->joinWith([
			'relationMeter',
			'relationMeter.relationMeterType',
			'relationSiteMeterTree',
		], 'LEFT JOIN')
		->andWhere([Meter::tableName(). '.site_id' => $site->id])
		->andWhere(SiteMeterTree::tableName(). '.parent_meter_channel_id IS NULL')
		->all();

		self::$tree = [
			'rows' => [],
			'totals' => [],
		];

		foreach ($meter_tree as $meter_channel) {
			self::generateMeterTree($site, $meter_channel, $from_date, $to_date);
		}

		$data['data'] = self::$tree;

		if (empty($data['data']['rows'])) {
			self::addError(Yii::t('common.report', 'The energy tree is empty for the site {name}.', ['name' => $site->name]));
		}

		return $data;
	}

	protected static function generateMeterTree(Site $model_site, MeterChannel $model, $from_date, $to_date)
	{
		$index = 0;

		if (($childrens = $model->relationSiteMeterTreeChildrens) != null) {
			if (($model_grandparent = $model->relationSiteMeterTree->relationParentMeterChannel) != null) {
				$rows[$index]['grandparent_name'] = $model_grandparent->relationMeter->name. ' - ' .$model_grandparent->getChannelName();
				$rows[$index]['grandparent_consumption'] = self::calculateTotalConsumptionMeterChannel($model_grandparent, $from_date, $to_date);
			}

			$consumption = self::calculateTotalConsumptionMeterChannel($model, $from_date, $to_date);
			$totals = [
				'parent_name' => $model->relationMeter->name. ' - ' .$model->getChannelName(),
				'parent_consumption' => $consumption,
			];
			$rows[$index]['parent_name'] = $model->relationMeter->name. ' - ' .$model->getChannelName();
			$rows[$index]['parent_consumption'] = $consumption;

			foreach ($childrens as $children) {
				$model_children = $children->relationMeterChannel;
				$tenants = ArrayHelper::map($model_site->getRelationTenantsToIssued()
				->andWhere([RuleSingleChannel::tableName(). '.channel_id' => $model_children->id])
				->all(), 'id', 'name');

				$consumption = self::calculateTotalConsumptionMeterChannel($model_children, $from_date, $to_date);

				$rows[$index]['tenants'] = implode("<br />", $tenants);
				$rows[$index]['children_name'] = "{$model_children->relationMeter->name} - {$model_children->getChannelName()}";
				$rows[$index]['children_consumption'] = $consumption;
				
				$totals['children_consumption']['total'] = ArrayHelper::getValue($totals, 'children_consumption.total', 0) + ArrayHelper::getValue($consumption, 'total', 0);
				$index++;

				self::generateMeterTree($model_site, $model_children, $from_date, $to_date);
			}

			array_unshift(self::$tree['rows'], $rows);
			array_unshift(self::$tree['totals'], $totals);
		}
	}

	protected static function calculateTotalConsumptionMeterChannel(MeterChannel $model_channel, $from_date, $to_date)
	{
		$consumption['total'] = 0;

		foreach ($model_channel->relationMeterSubchannels as $model_subchannel) {
			$consumption_from = ElectricityMeterRawData::getReadings($model_channel->relationMeter->name, $model_subchannel->channel, $from_date);
			$consumption_to = ElectricityMeterRawData::getReadings($model_channel->relationMeter->name, $model_subchannel->channel, $to_date);

			$consumption_shefel = ArrayHelper::getValue($consumption_to, 'shefel', 0) - ArrayHelper::getValue($consumption_from, 'shefel', 0);
			$consumption_geva = ArrayHelper::getValue($consumption_to, 'geva', 0) - ArrayHelper::getValue($consumption_from, 'geva', 0);
			$consumption_pisga = ArrayHelper::getValue($consumption_to, 'pisga', 0) - ArrayHelper::getValue($consumption_from, 'pisga', 0);
			$consumption['total'] += $consumption_shefel + $consumption_geva + $consumption_pisga;
		}

		return $consumption;
	}
}