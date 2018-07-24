<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150630_053529_alter_old_id extends Migration
{
	public function up()
	{
		$this->addColumn('meter', 'old_id', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->addColumn('meter_type', 'old_id', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->addColumn('meter_channel', 'old_id', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->addColumn('tenant', 'old_channel_id', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('meter', 'old_id');
		$this->dropColumn('meter_type', 'old_id');
		$this->dropColumn('meter_channel', 'old_id');
		$this->dropColumn('tenant', 'old_channel_id');
	}
}
