<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150701_050914_alter_table_site extends Migration
{
	public function up()
	{
		$this->addColumn('site', 'to_issue', Schema::TYPE_BOOLEAN . ' NOT NULL');
	}

	public function down()
	{
		$this->dropColumn('site', 'to_issue');
	}
}
