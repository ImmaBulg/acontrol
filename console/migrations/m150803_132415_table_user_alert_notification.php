<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150803_132415_table_user_alert_notification extends Migration
{
	public function up()
	{
		$this->createTable('user_alert_notification', [
			'id' => Schema::TYPE_PK,
			'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'site_owner_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_user_alert_notification_user_id', 'user_alert_notification', 'user_id', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_alert_notification_site_owner_id', 'user_alert_notification', 'site_owner_id', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_alert_notification_site_id', 'user_alert_notification', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_alert_notification_created_by', 'user_alert_notification', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_alert_notification_modified_by', 'user_alert_notification', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('user_alert_notification');
	}
}
