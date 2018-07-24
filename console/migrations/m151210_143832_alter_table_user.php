<?php

use yii\db\Schema;

use common\models\User;

class m151210_143832_alter_table_user extends \common\components\db\Migration
{
	public function up()
	{
		User::deleteAll(['role' => 'helpdesk']);

		$this->dropColumn('user', 'alert_notification_sms');
	}

	public function down()
	{
		$this->addColumn('user', 'alert_notification_sms', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
	}
}
