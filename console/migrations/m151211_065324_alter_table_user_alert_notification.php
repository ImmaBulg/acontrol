<?php

use yii\db\Schema;

class m151211_065324_alter_table_user_alert_notification extends \common\components\db\Migration
{
	public function up()
	{
		$this->dropForeignKey('FK_user_alert_notification_site_owner_id', 'user_alert_notification');
		$this->dropColumn('user_alert_notification', 'site_owner_id');
	}

	public function down()
	{
		$this->addColumn('user_alert_notification', 'site_owner_id', Schema::TYPE_INTEGER . ' NOT NULL');
		$this->addForeignKey('FK_user_alert_notification_site_owner_id', 'user_alert_notification', 'site_owner_id', 'user', 'id', 'CASCADE', 'RESTRICT');
	}
}
