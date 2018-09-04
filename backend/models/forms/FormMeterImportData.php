<?php

namespace backend\models\forms;

use api\models\AirMeterRawData;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\Meter;
use common\models\MeterType;
use common\models\MeterChannel;
use common\models\MeterSubchannel;
use common\models\MeterChannelMultiplier;
use common\models\MeterRawData;

/**
 * FormMeterImportData is the class for import data.
 */
class FormMeterImportData extends \yii\base\Model
{
	protected $_meter = false;

	public $meter_id;

	public function rules()
	{
		return [
			[['meter_id'], 'required'],
			[['meter_id'], 'integer'],
		];
	}

	public function attributeLabels()
	{
		return [
			'meter_id' => Yii::t('backend.meter', 'Meter ID'),
		];
	}

	public function loadAttributes(Meter $meter)
	{
		$this->_meter = $meter;
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$from_meter = Meter::findOne($this->meter_id);
		$from_meter_id = $from_meter->name;
		$to_meter = $this->_meter;
		$to_meter_id = $to_meter->name;
        $meter_type = $from_meter->type;
		$connection = Yii::$app->db;
		$transaction = $connection->beginTransaction();

		if ($meter_type === MeterType::TYPE_ELECTRICITY) {
            try	{
                $data = (new Query())
                    ->select([
                        'channel_id',
                        'date',
                        'shefel',
                        'geva',
                        'pisga',
                        'reading_shefel',
                        'reading_geva',
                        'reading_pisga',
                        'max_shefel',
                        'max_geva',
                        'max_pisga',
                        'status',
                        'created_at',
                        'modified_at',
                        'created_by',
                        'modified_by',
                        'export_shefel',
                        'export_geva',
                        'export_pisga',
                        'kvar_shefel',
                        'kvar_geva',
                        'kvar_pisga',
                    ])
                    ->from(MeterRawData::tableName())
                    ->andWhere(['meter_id' => $from_meter_id])
                    ->createCommand($connection)
                    ->queryAll();

                if ($data != null) {
                    MeterRawData::deleteAll(['meter_id' => $to_meter_id]);
                    MeterRawData::deleteCacheValue(["meter_raw_data:$to_meter_id"]);

                    array_walk($data, function(&$item) use ($to_meter_id) {
                        $item = array_values($item);
                        array_unshift($item, $to_meter_id);
                    });

                    $rows = $connection->createCommand()->batchInsert(MeterRawData::tableName(), [
                        'meter_id',
                        'channel_id',
                        'date',
                        'shefel',
                        'geva',
                        'pisga',
                        'reading_shefel',
                        'reading_geva',
                        'reading_pisga',
                        'max_shefel',
                        'max_geva',
                        'max_pisga',
                        'status',
                        'created_at',
                        'modified_at',
                        'created_by',
                        'modified_by',
                        'export_shefel',
                        'export_geva',
                        'export_pisga',
                        'kvar_shefel',
                        'kvar_geva',
                        'kvar_pisga',
                    ], $data)->execute();
                } else {
                    $rows = 0;
                }

                $transaction->commit();
                return $rows;
            } catch(Exception $e) {
                $transaction->rollback();
                throw new BadRequestHttpException($e->getMessage());
            }
        } else {
            try	{
                $data = (new Query())
                    ->select([
                        'kilowatt_hour',
                        'cubic_meter',
                        'kilowatt',
                        'cubic_meter_hour',
                        'incoming_temp',
                        'outgoing_temp',
                        'channel_id',
                        'created_by',
                        'modified_by',
                        'status',
                        'datetime',
                        'created_at',
                        'modified_at',
                        'cop',
                        'delta_t',
                    ])
                    ->from(AirMeterRawData::tableName())
                    ->andWhere(['meter_id' => $from_meter_id])
                    ->createCommand($connection)
                    ->queryAll();

                if ($data != null) {
                    AirMeterRawData::deleteAll(['meter_id' => $to_meter_id]);
                    AirMeterRawData::deleteCacheValue(["meter_raw_data:$to_meter_id"]);

                    array_walk($data, function(&$item) use ($to_meter_id) {
                        $item = array_values($item);
                        array_unshift($item, $to_meter_id);
                    });

                    $rows = $connection->createCommand()->batchInsert(AirMeterRawData::tableName(), [
                        'meter_id',
                        'kilowatt_hour',
                        'cubic_meter',
                        'kilowatt',
                        'cubic_meter_hour',
                        'incoming_temp',
                        'outgoing_temp',
                        'channel_id',
                        'created_by',
                        'modified_by',
                        'status',
                        'datetime',
                        'created_at',
                        'modified_at',
                        'cop',
                        'delta_t',
                    ], $data)->execute();
                } else {
                    $rows = 0;
                }

                $transaction->commit();
                return $rows;
            } catch(Exception $e) {
                $transaction->rollback();
                throw new BadRequestHttpException($e->getMessage());
            }
        }
	}

	public function getListMeters()
	{
		$rows = (new Query)->select(['meter.id', 'meter.name', 'meter_type.name as type'])
		->from(Meter::tableName(). ' meter')
		->innerJoin(MeterType::tableName(). ' meter_type', 'meter_type.id = meter.type_id')
		->andWhere([
			'and',
			['!=', 'meter.id', $this->_meter->id],
			['meter_type.id' => $this->_meter->type_id],
		])
		->all();

		return ArrayHelper::map($rows, 'id', function($value){
			return implode(' - ', [$value['name'], $value['type']]);
		});
	}
}
