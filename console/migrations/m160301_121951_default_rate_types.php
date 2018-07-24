<?php

use yii\db\Schema;
use common\models\RateType;

class m160301_121951_default_rate_types extends \common\components\db\Migration
{
	public function up()
	{
		$values = [
			[
				'name' => 'Home',
				'type' => 1, 
			],
			[
				'name' => 'General',
				'type' => 1, 
			],
			[
				'name' => 'Street lighting',
				'type' => 1, 
			],
			[
				'name' => 'Low',
				'type' => 2, 
			],
			[
				'name' => 'High',
				'type' => 2, 
			],
			[
				'name' => 'Supreme',
				'type' => 2, 
			],
			[
				'name' => 'AVG',
				'type' => 2, 
			],
			[
				'name' => 'Pisga mobile',
				'type' => 2, 
			],
		];

		foreach ($values as $value) {
			$model = new RateType();
			$model->name = $value['name'];
			$model->type = $value['type'];
			$model->save();
		}
	}

	public function down()
	{
		RateType::deleteAll();
	}
}
