<?php
namespace backend\models\searches\models;

use Yii;


/**
 * RuleFixedLoad search model.
 */
class RuleFixedLoad extends \common\models\RuleFixedLoad
{
	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['value', 'shefel', 'geva', 'pisga'], 'number'],
			['use_type', 'in', 'range' => array_keys(self::getListUseTypes()), 'skipOnEmpty' => true],
			['use_frequency', 'in', 'range' => array_keys(self::getListUseFrequencies()), 'skipOnEmpty' => true],
			['status', 'in', 'range' => array_keys(self::getListStatuses())],
		];
	}
}
