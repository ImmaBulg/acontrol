<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150803_121515_alter_table_user extends Migration
{
	public function up()
	{
		$this->addColumn('user', 'alert_notification_email', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
		$this->addColumn('user', 'alert_notification_sms', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
	}

	public function down(){
		$this->dropColumn('user', 'alert_notification_email');
		$this->dropColumn('user', 'alert_notification_sms');
	}
}
