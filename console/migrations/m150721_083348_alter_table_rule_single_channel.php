<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150721_083348_alter_table_rule_single_channel extends Migration
{
	public function up()
	{
		$this->addColumn('rule_single_channel', 'use_percent', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('rule_single_channel', 'use_percent');
	}
}
