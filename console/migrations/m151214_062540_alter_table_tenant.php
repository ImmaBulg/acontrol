<?php

use yii\db\Schema;
use common\models\Tenant;

class m151214_062540_alter_table_tenant extends \common\components\db\Migration
{
	public function up()
	{
		Tenant::updateAll(['square_meters' => 0], 'square_meters IS NULL'); 
	}

	public function down()
	{
		
	}
}
