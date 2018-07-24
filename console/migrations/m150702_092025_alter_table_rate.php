<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150702_092025_alter_table_rate extends Migration
{
	public function up()
	{
		$this->addColumn('rate', 'fixed_payment_bi', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
		$this->addColumn('rate', 'rate', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('rate', 'fixed_payment_bi');
		$this->dropColumn('rate', 'rate');
	}
}
