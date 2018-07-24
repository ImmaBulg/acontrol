<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150720_070940_alter_table_user_contact extends Migration
{
	public function up()
	{
		$this->addColumn('user_contact', 'cell_phone', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('user_contact', 'cell_phone');
	}
}
