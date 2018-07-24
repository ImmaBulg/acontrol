<?php

namespace frontend\models\forms;

use \DateTime;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\data\ArrayDataProvider;
use common\components\i18n\Formatter;
use common\models\Site;
use common\models\Tenant;
use common\models\Meter;
use common\models\MeterChannel;
use common\helpers\KwhCalculator;

/**
 * FormHistoryConsumption
 */
class FormHistoryConsumption extends \yii\base\Model
{
	const DRILLDOWN_DAILY = 'd';
	const DRILLDOWN_MONTHLY = 'm';

	protected $_tenant = false;

	public $from_date;
	public $to_date;
	public $drilldown;
	public $compare = 0;
	public $compare_from_date;
	public $compare_to_date;
	public $tenant_id;
	public $meter_id;
	public $channel_id;

	public function rules()
	{
		return [
			[['from_date', 'to_date', 'drilldown', 'compare', 'tenant_id'], 'required'],
			[['tenant_id', 'meter_id', 'channel_id'], 'integer'],
			[['from_date', 'to_date', 'compare_from_date', 'compare_to_date'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			[['to_date'], '\common\components\validators\DateTimeCompareValidator', 'compareAttribute' => 'from_date', 'format' => Formatter::PHP_DATE_FORMAT, 'operator' => '>='],
			[['compare_to_date'], '\common\components\validators\DateTimeCompareValidator', 'compareAttribute' => 'compare_from_date', 'format' => Formatter::PHP_DATE_FORMAT, 'operator' => '>='],
			// [['compare_to_date'], '\common\components\validators\DateTimeCompareValidator', 'compareAttribute' => 'to_date', 'format' => Formatter::PHP_DATE_FORMAT, 'operator' => '=', 'message' => Yii::t('frontend.view', '{compare_attribute} should be the same as {attribute}', [
			// 	'compare_attribute' => $this->getAttributeLabel('compare_to_date'),
			// 	'attribute' => $this->getAttributeLabel('to_date'),
			// ])],
			[['from_date', 'to_date', 'compare_from_date', 'compare_to_date'], '\common\components\validators\DateTimeCompareValidator', 'compareValue' => Yii::$app->formatter->asDate(time()), 'format' => Formatter::PHP_DATE_FORMAT, 'operator' => '<='],
			['drilldown', 'in', 'range' => array_keys(static::getListDrilldowns()), 'skipOnEmpty' => false],
			['compare', 'in', 'range' => array_keys(static::getListCompares()), 'skipOnEmpty' => false],
			[['from_date', 'to_date'], 'validateFromDate'],
			[['compare_from_date', 'compare_to_date'], 'required', 'when' => function($model) {
				return $model->compare == true;
			}, 'enableClientValidation' => false],
			// ['compare', 'validateCompare'],
		];
	}

	public function validateFromDate($attribute, $params)
	{
		if (!$this->hasErrors()) {
			switch ($this->drilldown) {
				case static::DRILLDOWN_MONTHLY:
					$from_date = (new DateTime($this->from_date));
					$to_date  = (new DateTime($this->to_date));
					$difference = $from_date->diff($to_date);

					if ($difference->y > 1 || ($difference->y == 1 && ($difference->m > 0 || $difference->d > 0))) {
						return $this->addError($attribute, Yii::t('frontend.view', 'Maximum {drilldown} drilldown is {n,plural,=0{# months} =1{# month} other{# months}}', [
							'n' => 12,
							'drilldown' => $this->getAliasDrilldown(),
						]));
					}
					break;
				
				case static::DRILLDOWN_DAILY:
				default:
					$from_date = (new DateTime($this->from_date));
					$to_date  = (new DateTime($this->to_date));
					$difference = $from_date->diff($to_date);

					if ($difference->m > 3 || ($difference->m == 3 && $difference->d > 0)) {
						return $this->addError($attribute, Yii::t('frontend.view', 'Maximum {drilldown} drilldown is {n,plural,=0{# months} =1{# month} other{# months}}', [
							'n' => 3,
							'drilldown' => $this->getAliasDrilldown(),
						]));
					}
					break;
			}
		}
	}

	// public function validateCompare($attribute, $params)
	// {
	// 	if ($this->compare == true) {
	// 		$compareFromValueDT = strtotime($this->compare_from_date);
	// 		$compareToValueDT = strtotime($this->compare_to_date);
	// 		$fromValueDT = strtotime('-1 year', strtotime($this->from_date));
	// 		$toValueDT = strtotime('-1 year', strtotime($this->to_date));

	// 		if ($compareFromValueDT < $fromValueDT) {
	// 			$this->addError('compare_from_date', Yii::t('frontend.view', '{attribute} must be greater than or equal to "{compareValue}".', [
	// 				'attribute' => $this->getAttributeLabel('compare_from_date'),
	// 				'compareValue' => Yii::$app->formatter->asDate($fromValueDT),
	// 			]));
	// 		}
	// 		if ($compareFromValueDT > $toValueDT) {
	// 			$this->addError('compare_from_date', Yii::t('frontend.view', '{attribute} must be less than or equal to "{compareValue}".', [
	// 				'attribute' => $this->getAttributeLabel('compare_from_date'),
	// 				'compareValue' => Yii::$app->formatter->asDate($toValueDT),
	// 			]));
	// 		}
	// 		if ($compareToValueDT < $fromValueDT) {
	// 			$this->addError('compare_to_date', Yii::t('frontend.view', '{attribute} must be greater than or equal to "{compareValue}".', [
	// 				'attribute' => $this->getAttributeLabel('compare_to_date'),
	// 				'compareValue' => Yii::$app->formatter->asDate($fromValueDT),
	// 			]));
	// 		}
	// 		if ($compareToValueDT > $toValueDT) {
	// 			$this->addError('compare_to_date', Yii::t('frontend.view', '{attribute} must be less than or equal to "{compareValue}".', [
	// 				'attribute' => $this->getAttributeLabel('compare_to_date'),
	// 				'compareValue' => Yii::$app->formatter->asDate($toValueDT),
	// 			]));
	// 		}
	// 	}
	// }

	public function attributeLabels()
	{
		return [
			'from_date' => Yii::t('frontend.view', 'From date'),
			'to_date' => Yii::t('frontend.view', 'To date'),
			'drilldown' => Yii::t('frontend.view', 'Drilldown'),
			'compare' => Yii::t('frontend.view', 'Compare to same period before'),
		];
	}

	public static function getListDrilldowns()
	{
		return [
			static::DRILLDOWN_DAILY => Yii::t('frontend.view', 'Daily'),
			static::DRILLDOWN_MONTHLY => Yii::t('frontend.view', 'Monthly'),
		];
	}

	public function getAliasDrilldown()
	{
		return ArrayHelper::getValue(static::getListDrilldowns(), $this->drilldown);
	}

	public static function getListCompares()
	{
		return [
			1 => Yii::t('frontend.view', 'Yes'),
			0 => Yii::t('frontend.view', 'No'),
		];
	}

	public function getAliasCompare()
	{
		return ArrayHelper::getValue(static::getListCompares(), $this->compare);
	}

	public function generateDataProvider()
	{
		$user = Yii::$app->user->identity;
		$allModels = [];

		if ($this->validate()) {
			$tenant = Tenant::findOne($this->tenant_id);
			$allModels = KwhCalculator::generate($tenant,  $this->channel_id, $this->from_date, $this->to_date, $this->drilldown);
		}

		return new ArrayDataProvider([
			'allModels' => $allModels,
			'sort' => [
				'attributes' => ['date', 'timestamp', 'pisga', 'geva', 'shefel', 'max_demand', 'kvar'],
			],
			'pagination' => false,
		]);
	}

	public function generateComparedDataProvider()
	{
		$user = Yii::$app->user->identity;
		$allModels = [];

		if ($this->validate() && $this->compare == true) {
			$tenant = Tenant::findOne($this->tenant_id);
			$meter = Meter::findOne($this->meter_id);
			$channel = MeterChannel::findOne($this->channel_id);

			$diff = (new DateTime($this->to_date))->diff(new DateTime($this->from_date));
			$compare_from_date = $this->compare_from_date;
			$compare_to_date = (new DateTime($this->compare_from_date))->modify("+{$diff->m} month")->modify("+{$diff->d} day")->format('d-m-Y');

			$allModels = KwhCalculator::generate($tenant, $meter, $channel, $compare_from_date, $compare_to_date, $this->drilldown);
		}

		return new ArrayDataProvider([
			'allModels' => $allModels,
			'sort' => [
				'attributes' => ['date', 'timestamp', 'pisga', 'geva', 'shefel', 'max_demand', 'kvar'],
			],
			'pagination' => false,
		]);
	}
}
