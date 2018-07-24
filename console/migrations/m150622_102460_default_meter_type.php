<?php

use yii\db\Schema;
use common\models\MeterType;
use common\components\db\Migration;

class m150622_102460_default_meter_type extends Migration
{
	public static function predefinedData()
	{
		return [
			[
				'name' => 'MC4 8x3',
				'channels' => 8,
				'phases' => 3,
			],
			[
				'name' => 'MC5 24x1',
				'channels' => 24,
				'phases' => 1,
			],
			[
				'name' => 'MC5 8x3',
				'channels' => 8,
				'phases' => 3,
			],
			[
				'name' => 'MCR24 24x1',
				'channels' => 24,
				'phases' => 1,
			],
			[
				'name' => 'RSM10 3x1',
				'channels' => 3,
				'phases' => 1,
			],
			[
				'name' => 'RSM10 1x3',
				'channels' => 1,
				'phases' => 3,
			],
			[
				'name' => 'RSM4 1x3',
				'channels' => 1,
				'phases' => 3,
			],
			[
				'name' => 'RSM4',
				'channels' => 1,
				'phases' => 1,
			],
			[
				'name' => 'RSM5 1x3',
				'channels' => 1,
				'phases' => 3,
			],
			[
				'name' => 'RSM5 3x1',
				'channels' => 3,
				'phases' => 1,
			],	
		];
	}

	public function up()
	{
		$transaction = Yii::$app->db->beginTransaction();
		
		try	{
			$data = self::predefinedData();

			foreach ($data as $value) {
				$model = new MeterType();
				$model->name = $value['name'];
				$model->channels = $value['channels'];
				$model->phases = $value['phases'];
				$model->save();
			}

			$transaction->commit();
		} catch(Exception $e) {
			$transaction->rollback();
			throw new Exception($e->getMessage());
		}
	}

	public function down(){
		$transaction = Yii::$app->db->beginTransaction();
		
		try	{
			$data = self::predefinedData();

			foreach ($data as $value) {
				$model = MeterType::findOne($value);

				if ($model != null) {
					$model->delete();
				}
			}

			$transaction->commit();
		} catch(Exception $e) {
			$transaction->rollback();
			throw new Exception($e->getMessage());
		}
	}
}
