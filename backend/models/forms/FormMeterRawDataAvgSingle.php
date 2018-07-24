<?php

namespace backend\models\forms;

use \DateTime;
use Yii;
use yii\db\Query;
use yii\web\BadRequestHttpException;

use common\models\MeterRawData;
use common\components\i18n\Formatter;

/**
 * FormMeterRawDataAvgSingle is the class for meter raw data autocomplete single avg.
 */
class FormMeterRawDataAvgSingle extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_meter_id;
	private $_channel_id;

	public $from_date;
	public $to_date;
	public $rule;

	public function rules()
	{
		return [
			[['from_date', 'to_date'], 'filter', 'filter' => 'trim'],
			[['from_date', 'to_date', 'rule'], 'required'],
			[['from_date', 'to_date'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['rule', 'in', 'range' => array_keys(MeterRawData::getListRulePeriods()), 'skipOnEmpty' => false],
			['rule', 'validateRule'],
		];
	}

	public function validateRule($attribute, $params)
	{
		if (!$this->hasErrors()) {
			$data = $this->getAvgData();
			$sql_date_format = Formatter::SQL_DATE_FORMAT;
			$date = $this->getDateFromPeriod();

			$model = MeterRawData::find()
			->andWhere([
				'meter_id' => $this->_meter_id,
				'channel_id' => $this->_channel_id,
			])
			->andWhere('(shefel IS NOT NULL OR reading_shefel IS NOT NULL) AND (geva IS NOT NULL OR reading_geva IS NOT NULL) AND (pisga IS NOT NULL OR reading_pisga IS NOT NULL)')
			->andWhere("DATE_FORMAT(FROM_UNIXTIME(date), '$sql_date_format') = :date", [
				'date' => Yii::$app->formatter->asDate($date - 86400, Formatter::PHP_DATE_FORMAT),
			])->one();

			if ($model == null) {
				return $this->addError($attribute, Yii::t('backend.meter', 'Readings for {date} must be set.', [
					'date' => Yii::$app->formatter->asDate($date - 86400),
				]));
			}

			$model = MeterRawData::find()
			->andWhere([
				'meter_id' => $this->_meter_id,
				'channel_id' => $this->_channel_id,
			])
			->andWhere('(shefel IS NOT NULL OR reading_shefel IS NOT NULL) AND (geva IS NOT NULL OR reading_geva IS NOT NULL) AND (pisga IS NOT NULL OR reading_pisga IS NOT NULL)')
			->andWhere("DATE_FORMAT(FROM_UNIXTIME(date), '$sql_date_format') = :date", [
				'date' => Yii::$app->formatter->asDate($date - 2 * 86400, Formatter::PHP_DATE_FORMAT),
			])->one();

			if ($model == null) {
				return $this->addError($attribute, Yii::t('backend.meter', 'Readings for {date} must be set.', [
					'date' => Yii::$app->formatter->asDate($date - 2 * 86400),
				]));
			}
		}
	}

	public function attributeLabels()
	{
		return [
			'from_date' => Yii::t('backend.meter', 'From'),
			'to_date' => Yii::t('backend.meter', 'To'),
		];
	}

	public function loadAttributes($model)
	{
		$this->_meter_id = $model->relationMeter->name;
		$this->_channel_id = $model->channel;
	}

	public function getAliasRule()
	{
		$list = MeterRawData::getListRulePeriods();
		return (isset($list[$this->rule])) ? $list[$this->rule] : $this->rule;
	}

	public function getDateFromPeriod()
	{
		$date = new DateTime($this->from_date);
		$date->modify('midnight');
		return $date->getTimestamp();
	}

	public function getDateToPeriod()
	{
		$date = new DateTime($this->to_date);
		$date->modify('tomorrow');
		return $date->getTimestamp() - 1;
	}

	public function getAvgData()
	{
		$from_date = $this->getDateFromPeriod();
		$to_date =  $this->getDateToPeriod();
		return MeterRawData::getAvgData($this->_meter_id, $this->_channel_id, $this->rule, $from_date, $to_date);
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$data = $this->getAvgData();
			$from_date = $this->getDateFromPeriod();
			$to_date = $this->getDateToPeriod();

			for ($i = $from_date; $i < $to_date; $i = $i + 86400) {
				$values = $data[Yii::$app->formatter->asDate($i)];
				$sql_date_format = Formatter::SQL_DATE_FORMAT;

				$model_previous = (new Query())
				->select('IFNULL(`shefel`, `reading_shefel`) as shefel, IFNULL(`geva`, `reading_geva`) as geva, IFNULL(`pisga`, `reading_pisga`) as pisga')
				->from(MeterRawData::tableName(). ' t')->andWhere([
					't.meter_id' => $this->_meter_id,
					't.channel_id' => $this->_channel_id,
				])->andWhere("DATE_FORMAT(FROM_UNIXTIME(date), '$sql_date_format') = :date", [
					'date' => Yii::$app->formatter->asDate($i - 86400, Formatter::PHP_DATE_FORMAT),
				])->one();

				if ($model_previous != null) {
					$model = MeterRawData::find()->where([
						'meter_id' => $this->_meter_id,
						'channel_id' => $this->_channel_id,
					])
					->andWhere("DATE_FORMAT(FROM_UNIXTIME(date), '$sql_date_format') = :date", [
						'date' => Yii::$app->formatter->asDate($i, Formatter::PHP_DATE_FORMAT),
					])->one();

					if ($model == null) {
						$model = new MeterRawData();
						$model->meter_id = $this->_meter_id;
						$model->channel_id = $this->_channel_id;
						$model->date = $i;
					}

					$model->shefel = $model_previous['shefel'] + $values[MeterRawData::CONSUMPTION_SHEFEL];
					$model->geva = $model_previous['geva'] + $values[MeterRawData::CONSUMPTION_GEVA];
					$model->pisga = $model_previous['pisga'] + $values[MeterRawData::CONSUMPTION_PISGA];

					if (!$model->save()) {
						throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
					}

					MeterRawData::deleteCacheValue(["meter_raw_data:{$model->relationMeter->name}_{$model->relationMeterChannel->channel}"]);
				}
			}

			$transaction->commit();
			return true;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}
}
