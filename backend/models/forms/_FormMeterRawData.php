<?php

namespace backend\models\forms;

use \DateTime;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\data\ArrayDataProvider;

use common\models\Meter;
use common\models\MeterRawData;
use common\components\i18n\Formatter;

/**
 * FormMeterRawData is the class for meter raw data create/edit.
 */
class FormMeterRawData extends \yii\base\Model
{
	const PAGE_SIZE = 100;
	const PAGE_PARAM = 'page';
	
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_meter_id;
	private $_channel_id;
	private $_from_date;
	private $_to_date;
	private $_avg_data;
	private $_direction = true;
	private $_data_provider;

	public $consumption;
	public $readings;

	public function rules()
	{
		return [
			[['consumption'], function($attribute, $params){
				$consumption = (array) $this->$attribute;

				foreach ($consumption as $date => $values) {
					foreach ($values as $key => $value) {
						if ($value != null) {
							if (!preg_match('/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/', $value)) {
								$this->addError($attribute. '[' .$date. '][' .$key. ']', Yii::t('backend.meter', '{attribute} for {date} must be a number.', [
									'attribute' => $this->getAttributeLabel($attribute),
									'date' => $key,
								]));
							} elseif($value < 0) {
								$this->addError($attribute. '[' .$date. '][' .$key. ']', Yii::t('backend.meter', '{attribute} for {date} must be no less than 0.', [
									'attribute' => $this->getAttributeLabel($attribute),
									'date' => $key,
								]));							
							}
						}
					}
				}
			}],
		];
	}

	public function attributeLabels()
	{
		return [
			'date' => Yii::t('backend.meter', 'Reading date'),
			'shefel' => Yii::t('backend.meter', 'Shefel'),
			'shefel_avg' => Yii::t('backend.meter', 'AVG'),
			'max_shefel' => Yii::t('backend.meter', 'Max shefel'),
			'export_shefel' => Yii::t('backend.meter', 'Export shefel'),
			'geva' => Yii::t('backend.meter', 'Geva'),
			'geva_avg' => Yii::t('backend.meter', 'AVG'),
			'max_geva' => Yii::t('backend.meter', 'Max geva'),
			'export_geva' => Yii::t('backend.meter', 'Export geva'),
			'pisga' => Yii::t('backend.meter', 'Pisga'),
			'pisga_avg' => Yii::t('backend.meter', 'AVG'),
			'max_pisga' => Yii::t('backend.meter', 'Max pisga'),
			'export_pisga' => Yii::t('backend.meter', 'Export pisga'),
		];
	}

	public function getDataProvider()
	{
		if ($this->_data_provider == null) {
			$this->_data_provider = $this->generateDataProvider();
		}

		return $this->_data_provider;
	}

	private function generateDataProvider()
	{
		$data = [];
		$from_date = $this->getDateFromPeriod();
		$to_date =  $this->getDateToPeriod();
		$rows = $this->getQueryRows();
		$sql_date_format = Formatter::SQL_DATE_FORMAT;

		$avg_readings = [];
		$avg_data = $this->_avg_data;
		$direction = $this->_direction;
		$apply_avg = Yii::$app->request->getQueryParam('avg');

		if ($direction) {
			for ($i = $from_date; $i < $to_date; $i = $i + 86400) {
				$date = Yii::$app->formatter->asDate($i);
				$data[$date] = [
					'date' => $date,
					'timestamp' => $i,
					'meter_id' => $this->_meter_id,
					'channel_id' => $this->_channel_id,
					'class' => 'danger',
					'input_class' => 'form-control-default',
				];

				if (isset($rows[$date])) {
					$data[$date] = ArrayHelper::merge($rows[$date], $data[$date]);
					$this->consumption[$date]['shefel'] = $rows[$date]['shefel'];
					$this->consumption[$date]['geva'] = $rows[$date]['geva'];
					$this->consumption[$date]['pisga'] = $rows[$date]['pisga'];
					$this->consumption[$date]['max_shefel'] = $rows[$date]['max_shefel'];
					$this->consumption[$date]['max_geva'] = $rows[$date]['max_geva'];
					$this->consumption[$date]['max_pisga'] = $rows[$date]['max_pisga'];
					$this->consumption[$date]['export_shefel'] = $rows[$date]['export_shefel'];
					$this->consumption[$date]['export_geva'] = $rows[$date]['export_geva'];
					$this->consumption[$date]['export_pisga'] = $rows[$date]['export_pisga'];
					$this->consumption[$date]['kvar_shefel'] = $rows[$date]['kvar_shefel'];
					$this->consumption[$date]['kvar_geva'] = $rows[$date]['kvar_geva'];
					$this->consumption[$date]['kvar_pisga'] = $rows[$date]['kvar_pisga'];

					$this->readings[$date]['shefel'] = $rows[$date]['reading_shefel'];
					$this->readings[$date]['geva'] = $rows[$date]['reading_geva'];
					$this->readings[$date]['pisga'] = $rows[$date]['reading_pisga'];

					$avg_readings[$date] = [
						'shefel' => ($reading_shefel = ArrayHelper::getValue($rows[$date], 'shefel', 0)) ? $reading_shefel : ArrayHelper::getValue($rows[$date], 'reading_shefel', 0),
						'geva' => ($reading_geva = ArrayHelper::getValue($rows[$date], 'geva', 0)) ? $reading_geva : ArrayHelper::getValue($rows[$date], 'reading_geva', 0),
						'pisga' => ($reading_pisga = ArrayHelper::getValue($rows[$date], 'pisga', 0)) ? $reading_pisga : ArrayHelper::getValue($rows[$date], 'reading_pisga', 0),
						'export_shefel' => ArrayHelper::getValue($rows[$date], 'export_shefel', 0),
						'export_geva' => ArrayHelper::getValue($rows[$date], 'export_geva', 0),
						'export_pisga' => ArrayHelper::getValue($rows[$date], 'export_pisga', 0),
					];

					if (!empty($rows[$date]['created_by']) || !empty($rows[$date]['modified_by'])) {
						$data[$date]['class'] = 'warning';
					} else {
						$data[$date]['class'] = 'default';
					}
				} elseif($apply_avg) {
					$previous_date = Yii::$app->formatter->asDate($i - 86400);

					if (!empty($avg_readings[$previous_date])) {
						$this->consumption[$date]['shefel'] = $avg_readings[$previous_date]['shefel'] + $avg_data[$date]['shefel'];
						$this->consumption[$date]['geva'] = $avg_readings[$previous_date]['geva'] + $avg_data[$date]['geva'];
						$this->consumption[$date]['pisga'] = $avg_readings[$previous_date]['pisga'] + $avg_data[$date]['pisga'];
						$this->consumption[$date]['export_shefel'] = $avg_readings[$previous_date]['export_shefel'] + $avg_data[$date]['export_shefel'];
						$this->consumption[$date]['export_geva'] = $avg_readings[$previous_date]['export_geva'] + $avg_data[$date]['export_geva'];
						$this->consumption[$date]['export_pisga'] = $avg_readings[$previous_date]['export_pisga'] + $avg_data[$date]['export_pisga'];

						$avg_readings[$date] = [
							'shefel' => $avg_readings[$previous_date]['shefel'] + $avg_data[$date]['shefel'],
							'geva' => $avg_readings[$previous_date]['geva'] + $avg_data[$date]['geva'],
							'pisga' => $avg_readings[$previous_date]['pisga'] + $avg_data[$date]['pisga'],
							'export_shefel' => $avg_readings[$previous_date]['export_shefel'] + $avg_data[$date]['export_shefel'],
							'export_geva' => $avg_readings[$previous_date]['export_geva'] + $avg_data[$date]['export_geva'],
							'export_pisga' => $avg_readings[$previous_date]['export_pisga'] + $avg_data[$date]['export_pisga'],
						];

						$data[$date]['class'] = 'default';
						$data[$date]['input_class'] = 'form-control-danger';
					} else {
						$previous_data = (new Query())
						->select('IFNULL(`shefel`, `reading_shefel`) as shefel, IFNULL(`geva`, `reading_geva`) as geva, IFNULL(`pisga`, `reading_pisga`) as pisga, export_shefel, export_geva, export_pisga')
						->from(MeterRawData::tableName(). ' t')->andWhere([
							't.meter_id' => $this->_meter_id,
							't.channel_id' => $this->_channel_id,
						])->andWhere("DATE_FORMAT(FROM_UNIXTIME(date), '$sql_date_format') = :date", [
							'date' => Yii::$app->formatter->asDate($previous_date, Formatter::PHP_DATE_FORMAT),
						])->one();

						if ($previous_data != null) {
							$this->consumption[$date]['shefel'] = $previous_data['shefel'] + $avg_data[$date]['shefel'];
							$this->consumption[$date]['geva'] = $previous_data['geva'] + $avg_data[$date]['geva'];
							$this->consumption[$date]['pisga'] = $previous_data['pisga'] + $avg_data[$date]['pisga'];
							$this->consumption[$date]['export_shefel'] = $previous_data['export_shefel'] + $avg_data[$date]['export_shefel'];
							$this->consumption[$date]['export_geva'] = $previous_data['export_geva'] + $avg_data[$date]['export_geva'];
							$this->consumption[$date]['export_pisga'] = $previous_data['export_pisga'] + $avg_data[$date]['export_pisga'];

							$avg_readings[$date] = [
								'shefel' => $previous_data['shefel'] + $avg_data[$date]['shefel'],
								'geva' => $previous_data['geva'] + $avg_data[$date]['geva'],
								'pisga' => $previous_data['pisga'] + $avg_data[$date]['pisga'],
								'export_shefel' => $previous_data['export_shefel'] + $avg_data[$date]['export_shefel'],
								'export_geva' => $previous_data['export_geva'] + $avg_data[$date]['export_geva'],
								'export_pisga' => $previous_data['export_pisga'] + $avg_data[$date]['export_pisga'],
							];

							$data[$date]['class'] = 'default';
							$data[$date]['input_class'] = 'form-control-danger';
						}
					}
				}
			}
		} else {
			for ($i = $to_date; $i >= $from_date; $i = $i - 86400) {
				$date = Yii::$app->formatter->asDate($i);
				$data[$date] = [
					'date' => $date,
					'timestamp' => $i,
					'meter_id' => $this->_meter_id,
					'channel_id' => $this->_channel_id,
					'class' => 'danger',
					'input_class' => 'form-control-default',
				];

				if (isset($rows[$date])) {
					$data[$date] = ArrayHelper::merge($rows[$date], $data[$date]);
					$this->consumption[$date]['shefel'] = $rows[$date]['shefel'];
					$this->consumption[$date]['geva'] = $rows[$date]['geva'];
					$this->consumption[$date]['pisga'] = $rows[$date]['pisga'];
					$this->consumption[$date]['max_shefel'] = $rows[$date]['max_shefel'];
					$this->consumption[$date]['max_geva'] = $rows[$date]['max_geva'];
					$this->consumption[$date]['max_pisga'] = $rows[$date]['max_pisga'];
					$this->consumption[$date]['export_shefel'] = $rows[$date]['export_shefel'];
					$this->consumption[$date]['export_geva'] = $rows[$date]['export_geva'];
					$this->consumption[$date]['export_pisga'] = $rows[$date]['export_pisga'];

					$this->readings[$date]['shefel'] = $rows[$date]['reading_shefel'];
					$this->readings[$date]['geva'] = $rows[$date]['reading_geva'];
					$this->readings[$date]['pisga'] = $rows[$date]['reading_pisga'];

					$avg_readings[$date] = [
						'shefel' => ($reading_shefel = ArrayHelper::getValue($rows[$date], 'shefel', 0)) ? $reading_shefel : ArrayHelper::getValue($rows[$date], 'reading_shefel', 0),
						'geva' => ($reading_geva = ArrayHelper::getValue($rows[$date], 'geva', 0)) ? $reading_geva : ArrayHelper::getValue($rows[$date], 'reading_geva', 0),
						'pisga' => ($reading_pisga = ArrayHelper::getValue($rows[$date], 'pisga', 0)) ? $reading_pisga : ArrayHelper::getValue($rows[$date], 'reading_pisga', 0),
						'export_shefel' => ArrayHelper::getValue($rows[$date], 'export_shefel', 0),
						'export_geva' => ArrayHelper::getValue($rows[$date], 'export_geva', 0),
						'export_pisga' => ArrayHelper::getValue($rows[$date], 'export_pisga', 0),
					];

					if (!empty($rows[$date]['created_by']) || !empty($rows[$date]['modified_by'])) {
						$data[$date]['class'] = 'warning';
					} else {
						$data[$date]['class'] = 'default';
					}
				} elseif($apply_avg) {
					$next_date = Yii::$app->formatter->asDate($i + 86400);

					if (!empty($avg_readings[$next_date])) {
						$this->consumption[$date]['shefel'] = ($avg_readings[$next_date]['shefel'] - $avg_data[$date]['shefel'] >= 0) ? $avg_readings[$next_date]['shefel'] - $avg_data[$date]['shefel'] : 0;
						$this->consumption[$date]['geva'] = ($avg_readings[$next_date]['geva'] - $avg_data[$date]['geva'] >= 0) ? $avg_readings[$next_date]['geva'] - $avg_data[$date]['geva'] : 0;
						$this->consumption[$date]['pisga'] = ($avg_readings[$next_date]['pisga'] - $avg_data[$date]['pisga'] >= 0) ? $avg_readings[$next_date]['pisga'] - $avg_data[$date]['pisga'] : 0;
						$this->consumption[$date]['export_shefel'] = ($avg_readings[$next_date]['export_shefel'] - $avg_data[$date]['export_shefel'] >= 0) ? $avg_readings[$next_date]['export_shefel'] - $avg_data[$date]['export_shefel'] : 0;
						$this->consumption[$date]['export_geva'] = ($avg_readings[$next_date]['export_geva'] - $avg_data[$date]['export_geva'] >= 0) ? $avg_readings[$next_date]['export_geva'] - $avg_data[$date]['export_geva'] : 0;
						$this->consumption[$date]['export_pisga'] = ($avg_readings[$next_date]['export_pisga'] - $avg_data[$date]['export_pisga'] >= 0) ? $avg_readings[$next_date]['export_pisga'] - $avg_data[$date]['export_pisga'] : 0;

						$avg_readings[$date] = [
							'shefel' => ($avg_readings[$next_date]['shefel'] - $avg_data[$date]['shefel'] >= 0) ? $avg_readings[$next_date]['shefel'] - $avg_data[$date]['shefel'] : 0,
							'geva' => ($avg_readings[$next_date]['geva'] - $avg_data[$date]['geva'] >= 0) ? $avg_readings[$next_date]['geva'] - $avg_data[$date]['geva'] : 0,
							'pisga' => ($avg_readings[$next_date]['pisga'] - $avg_data[$date]['pisga'] >= 0) ? $avg_readings[$next_date]['pisga'] - $avg_data[$date]['pisga'] : 0,
							'export_shefel' => ($avg_readings[$next_date]['export_shefel'] - $avg_data[$date]['export_shefel'] >= 0) ? $avg_readings[$next_date]['export_shefel'] - $avg_data[$date]['export_shefel'] : 0,
							'export_geva' => ($avg_readings[$next_date]['export_geva'] - $avg_data[$date]['export_geva'] >= 0) ? $avg_readings[$next_date]['export_geva'] - $avg_data[$date]['export_geva'] : 0,
							'export_pisga' => ($avg_readings[$next_date]['export_pisga'] - $avg_data[$date]['export_pisga'] >= 0) ? $avg_readings[$next_date]['export_pisga'] - $avg_data[$date]['export_pisga'] : 0,
						];

						$data[$date]['class'] = 'default';
						$data[$date]['input_class'] = 'form-control-danger';
					} else {
						$previous_data = (new Query())
						->select('IFNULL(`shefel`, `reading_shefel`) as shefel, IFNULL(`geva`, `reading_geva`) as geva, IFNULL(`pisga`, `reading_pisga`) as pisga, export_shefel, export_geva, export_pisga')
						->from(MeterRawData::tableName(). ' t')->andWhere([
							't.meter_id' => $this->_meter_id,
							't.channel_id' => $this->_channel_id,
						])->andWhere("DATE_FORMAT(FROM_UNIXTIME(date), '$sql_date_format') = :date", [
							'date' => Yii::$app->formatter->asDate($next_date, Formatter::PHP_DATE_FORMAT),
						])->one();

						if ($previous_data != null) {
							$this->consumption[$date]['shefel'] = ($previous_data['shefel'] - $avg_data[$date]['shefel'] >= 0) ? $previous_data['shefel'] - $avg_data[$date]['shefel'] : 0;
							$this->consumption[$date]['geva'] = ($previous_data['geva'] - $avg_data[$date]['geva'] >= 0) ? $previous_data['geva'] - $avg_data[$date]['geva'] : 0;
							$this->consumption[$date]['pisga'] = ($previous_data['pisga'] - $avg_data[$date]['pisga'] >= 0) ? $previous_data['pisga'] - $avg_data[$date]['pisga'] : 0;
							$this->consumption[$date]['export_shefel'] = ($previous_data['export_shefel'] - $avg_data[$date]['export_shefel'] >= 0) ? $previous_data['export_shefel'] - $avg_data[$date]['export_shefel'] : 0;
							$this->consumption[$date]['export_geva'] = ($previous_data['export_geva'] - $avg_data[$date]['export_geva'] >= 0) ? $previous_data['export_geva'] - $avg_data[$date]['export_geva'] : 0;
							$this->consumption[$date]['export_pisga'] = ($previous_data['export_pisga'] - $avg_data[$date]['export_pisga'] >= 0) ? $previous_data['export_pisga'] - $avg_data[$date]['export_pisga'] : 0;

							$avg_readings[$date] = [
								'shefel' => ($previous_data['shefel'] - $avg_data[$date]['shefel'] >= 0) ? $previous_data['shefel'] - $avg_data[$date]['shefel'] : 0,
								'geva' => ($previous_data['geva'] - $avg_data[$date]['geva'] >= 0) ? $previous_data['geva'] - $avg_data[$date]['geva'] : 0,
								'pisga' => ($previous_data['pisga'] - $avg_data[$date]['pisga'] >= 0) ? $previous_data['pisga'] - $avg_data[$date]['pisga'] : 0,
								'export_shefel' => ($previous_data['export_shefel'] - $avg_data[$date]['export_shefel'] >= 0) ? $previous_data['export_shefel'] - $avg_data[$date]['export_shefel'] : 0,
								'export_geva' => ($previous_data['export_geva'] - $avg_data[$date]['export_geva'] >= 0) ? $previous_data['export_geva'] - $avg_data[$date]['export_geva'] : 0,
								'export_pisga' => ($previous_data['export_pisga'] - $avg_data[$date]['export_pisga'] >= 0) ? $previous_data['export_pisga'] - $avg_data[$date]['export_pisga'] : 0,
							];

							$data[$date]['class'] = 'default';
							$data[$date]['input_class'] = 'form-control-danger';
						}
					}
				}
			}

			uasort($data, function($a, $b) {
				return $a['timestamp'] - $b['timestamp'];
			});
		}

		return new ArrayDataProvider([
			'allModels' => $data,
			'pagination' => [
				'pageParam' => self::PAGE_PARAM,
				'defaultPageSize' => self::PAGE_SIZE,
				'pageSizeLimit' => [
					1,
					self::PAGE_SIZE,
				],
			],
		]);
	}

	private function getQueryRows()
	{
		$from_date = $this->getDateFromPeriod();
		$to_date = $this->getDateToPeriod();

		$query = (new Query())->from(MeterRawData::tableName(). ' t')
		->andWhere([
			't.meter_id' => $this->_meter_id,
			't.channel_id' => $this->_channel_id,
		])->andWhere('date >= :from_date AND date <= :to_date', [
			'from_date' => $from_date,
			'to_date' => $to_date,
		]);

		$result = $query->orderBy(['t.date' => SORT_ASC])->all();
		return ArrayHelper::map($result, function($model){
			return Yii::$app->formatter->asDate($model['date']);
		}, function($model){
			return $model;
		});
	}

	public function loadAttributes($model)
	{
		$this->_meter_id = $model->relationMeter->name;
		$this->_channel_id = $model->channel;
	}

	public function loadFilters($form)
	{
		$this->_from_date = $form->from_date;
		$this->_to_date = $form->to_date;
	}

	public function loadAvgData($form)
	{
		$this->_avg_data = $form->getAvgData();
		$this->_direction = $form->direction;
	}

    protected function getDateFromPeriod() {
        if($this->_from_date != null) {
            $date = new DateTime($this->_from_date);
            $date->modify('midnight');
            return $date;
        }
    }

    protected function getDateToPeriod() {
        if($this->_to_date != null) {
            $date = new DateTime($this->_to_date);
            $date->modify('tomorrow + 24 hours -1 second');
            return $date;
        }
    }
 
	public function save()
	{
		if (!$this->validate()) return false;
		if ($this->consumption == null) return true;

		$consumption = $this->consumption;
		$data_provider = $this->getDataProvider();
		$data_provider->prepare();
		$page = $data_provider->getPagination()->getPage();
		$from_date = $this->getDateFromPeriod() + ($page * self::PAGE_SIZE * 86400);
		$to_date = $from_date + (self::PAGE_SIZE * 86400);
		$sql_date_format = Formatter::SQL_DATE_FORMAT;
		$transaction = Yii::$app->db->beginTransaction();

		try	{
			foreach ($consumption as $date => $values) {
				$timestamp = Yii::$app->formatter->asTimestamp($date);
				$model = MeterRawData::find()->where([
					'meter_id' => $this->_meter_id,
					'channel_id' => $this->_channel_id,
				])->andWhere("DATE_FORMAT(FROM_UNIXTIME(date), '$sql_date_format') = :date", [
					'date' => Yii::$app->formatter->asDate($date, Formatter::PHP_DATE_FORMAT),
				])->one();

				if ($model == null) {
					$model = new MeterRawData();
					$model->meter_id = $this->_meter_id;
					$model->channel_id = $this->_channel_id;
					$model->date = $date;

					$values = array_filter($values, function($item) {
						return ($item != null);
					});
				}

				if ($values != null) {
					$model->attributes = $values;

					if ($model->oldAttributes != $model->attributes) {
						if (!$model->save()) {
							throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
						}

						MeterRawData::deleteCacheValue(["meter_raw_data:{$model->relationMeter->name}_{$model->relationMeterChannel->channel}"]);
					}
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
