<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150720_074555_alter_table_rate extends Migration
{
	public function up()
	{
		$this->addColumn('rate', 'shefel_identifier', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->addColumn('rate', 'geva_identifier', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->addColumn('rate', 'pisga_identifier', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('rate', 'shefel_identifier');
		$this->dropColumn('rate', 'geva_identifier');
		$this->dropColumn('rate', 'pisga_identifier');
	}
}
