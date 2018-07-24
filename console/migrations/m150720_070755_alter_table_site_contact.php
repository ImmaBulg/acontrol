<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150720_070755_alter_table_site_contact extends Migration
{
	public function up()
	{
		$this->addColumn('site_contact', 'cell_phone', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('site_contact', 'cell_phone');
	}
}
