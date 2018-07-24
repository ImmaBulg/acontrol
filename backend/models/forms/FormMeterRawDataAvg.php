<?php

namespace backend\models\forms;

use \DateTime;
use Yii;
use yii\web\BadRequestHttpException;

use common\models\MeterRawData;
use common\components\i18n\Formatter;

/**
 * FormMeterRawDataAvg is the class for meter raw data autocomplete avg.
 */
class FormMeterRawDataAvg extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_meter_id;
	private $_channel_id;

	public $from_date;
	public $to_date;
	public $period_from;
	public $period_to;
	public $direction = true;

	public function rules()
	{
		return [
			[['from_date', 'to_date', 'period_from', 'period_to'], 'filter', 'filter' => 'trim'],
			[['period_from', 'period_to'], 'required'],
			[['from_date', 'to_date', 'period_from', 'period_to'], 'date', 'format' => Formatter::PHP_DATE_TIME_FORMAT],
			[['direction'], 'boolean'],
		];
	}

	public function attributeLabels()
	{
		return [
			'direction' => Yii::t('backend.meter', 'Direction'),
			'from_date' => Yii::t('backend.meter', 'From'),
			'to_date' => Yii::t('backend.meter', 'To'),
			'period_from' => Yii::t('backend.meter', 'From'),
			'period_to' => Yii::t('backend.meter', 'To'),
		];
	}

    public function loadDefaultAttributes()
    {
        $this->period_from = Yii::$app->formatter->asDateTime((new DateTime('today midnight')));
        $this->period_to = Yii::$app->formatter->asDateTime((new DateTime('tomorrow midnight')));
    }


    public function loadAttributes($model)
	{
		$this->_meter_id = $model->relationMeter->name;
		$this->_channel_id = $model->channel;
	}

	public function loadFilters($form)
	{
		$this->from_date = $form->from_date;
		$this->to_date = $form->to_date;
	}

	public static function getDateFromPeriod($date)
	{
		if ($date != null) {
			$date = new DateTime($date);
			return $date->getTimestamp();
		}
	}

	public static function getDateToPeriod($date)
	{
		if ($date != null) {
			$date = new DateTime($date);
			return $date->getTimestamp();
		}
	}

	public function getAvgData()
	{
		if ($this->period_from != null && $this->period_to != null && $this->from_date != null && $this->to_date != null) {
			$period_from = static::getDateFromPeriod($this->period_from);
			$period_to =  static::getDateToPeriod($this->period_to);
			$from_date = static::getDateFromPeriod($this->from_date);
			$to_date =  static::getDateToPeriod($this->to_date);
			
			return MeterRawData::getAvgData($this->_meter_id, $this->_channel_id, $period_from, $period_to, $from_date, $to_date);
		}
	}
}
