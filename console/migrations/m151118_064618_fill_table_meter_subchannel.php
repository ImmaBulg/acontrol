<?php

use yii\db\Schema;
use yii\console\Exception;

use common\models\MeterChannel;
use common\models\MeterSubchannel;

class m151118_064618_fill_table_meter_subchannel extends \common\components\db\Migration
{
	public function up()
	{
		$transaction = Yii::$app->db->beginTransaction();
		$model_meter_channels = MeterChannel::find()->all();

		try	{
			foreach ($model_meter_channels as $model_meter_channel) {
				$channels = $model_meter_channel->relationMeter->relationMeterType->channels;
				$phases = $model_meter_channel->relationMeter->relationMeterType->phases;
			
				if ($phases > 1) {
					$channel = 1;
					for ($i = 1; $i <= $channels; $i++) {
						for ($j = 0; $j < $phases; $j++) {
							if ($i == $model_meter_channel->channel) {
								$model = new MeterSubchannel();
								$model->meter_id = $model_meter_channel->meter_id;
								$model->channel_id = $model_meter_channel->id;
								$model->channel = $channel;

								if (!$model->save()) {
									throw new Exception(implode(' ', $model->getFirstErrors()));
								}
							}

							$channel++;
						}
					}
				} else {
					$model = new MeterSubchannel();
					$model->meter_id = $model_meter_channel->meter_id;
					$model->channel_id = $model_meter_channel->id;
					$model->channel = $model_meter_channel->channel;
				
					if (!$model->save()) {
						throw new Exception(implode(' ', $model->getFirstErrors()));
					}
				}
			}

			$transaction->commit();
		} catch(Exception $e) {
			$transaction->rollback();
			throw new Exception($e->getMessage());
		}
	}

	public function down()
	{
		MeterSubchannel::deleteAll();
	}
}
