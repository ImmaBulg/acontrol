<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150624_090626_alter_old_id extends Migration
{
	public function up()
	{
		$this->addColumn('user', 'old_id', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->addColumn('user_contact', 'old_id', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->addColumn('site', 'old_id', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->addColumn('site_contact', 'old_id', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->addColumn('tenant', 'old_id', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->addColumn('tenant_contact', 'old_id', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('user', 'old_id');
		$this->dropColumn('user_contact', 'old_id');
		$this->dropColumn('site', 'old_id');
		$this->dropColumn('site_contact', 'old_id');
		$this->dropColumn('tenant', 'old_id');
		$this->dropColumn('tenant_contact', 'old_id');
	}
}
