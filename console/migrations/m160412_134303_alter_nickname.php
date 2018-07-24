<?php

use yii\db\Schema;

class m160412_134303_alter_nickname extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('user', 'nickname', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('user', 'nickname');
	}
}
