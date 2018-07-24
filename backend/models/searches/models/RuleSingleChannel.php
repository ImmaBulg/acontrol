<?php
namespace backend\models\searches\models;

use Yii;

use common\components\i18n\Formatter;

/**
 * RuleSingleChannel search model.
 */
class RuleSingleChannel extends \common\models\RuleSingleChannel
{
	public $meter_name;
	public $channel_name;
	public $usage_tenant_name;

	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['meter_name', 'usage_tenant_name'], 'string'],
			[['channel_name'], 'integer'],
			['use_type', 'in', 'range' => array_keys(self::getListUseTypes())],
			['use_percent', 'in', 'range' => array_keys(self::getListUsePercents())],
			['percent', 'number', 'min' => 0, 'max' => 100],
			[['start_date'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['from_hours', 'date', 'format' => Formatter::PHP_TIME_FORMAT],
			['to_hours', 'date', 'format' => Formatter::PHP_TIME_FORMAT],
			['total_bill_action', 'in', 'range' => array_keys(self::getListTotalBillActions()), 'skipOnEmpty' => true],
			['status', 'in', 'range' => array_keys(self::getListStatuses())],
		];
	}
}
