<?php
namespace backend\models\searches\models;

use Yii;


/**
 * RuleGroupLoad search model.
 */
class RuleGroupLoad extends \common\models\RuleGroupLoad
{
	public $meter_name;
	public $channel_name;
	public $group_name;

	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['name', 'meter_name', 'group_name'], 'string'],
			[['channel_name'], 'integer'],
			['use_type', 'in', 'range' => array_keys(self::getListUseTypes())],
			['use_percent', 'in', 'range' => array_keys(self::getListUsePercents())],
			['total_bill_action', 'in', 'range' => array_keys(self::getListTotalBillActions()), 'skipOnEmpty' => true],
			['status', 'in', 'range' => array_keys(self::getListStatuses())],
		];
	}
}
