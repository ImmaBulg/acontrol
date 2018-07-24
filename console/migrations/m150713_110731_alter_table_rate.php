<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150713_110731_alter_table_rate extends Migration
{
	public function up()
	{
		$this->addColumn('rate', 'identifier', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->dropColumn('rate', 'day_type');
		$this->dropColumn('rate', 'fixed_payment_bi');
	}

	public function down()
	{
		$this->dropColumn('rate', 'identifier');
		$this->addColumn('rate', 'day_type', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
		$this->addColumn('rate', 'fixed_payment_bi', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
	}
}
