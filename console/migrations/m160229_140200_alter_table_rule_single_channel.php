<?php

use yii\db\Schema;
use common\models\RuleSingleChannel;

class m160229_140200_alter_table_rule_single_channel extends \common\components\db\Migration
{
	public function up()
	{
		$this->dropColumn('rule_single_channel', 'percent');
		$this->renameColumn('rule_single_channel', 'use_percent', 'percent');
		
		$this->renameColumn('rule_single_channel', 'use_type', 'use_percent');
		$this->addColumn('rule_single_channel', 'use_type', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
		RuleSingleChannel::updateAll(['use_type' => 1]);

		$this->addColumn('rule_single_channel', 'usage_tenant_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_rule_single_channel_usage_tenant_id', 'rule_single_channel', 'usage_tenant_id', 'tenant', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropColumn('rule_single_channel', 'use_type');
		$this->renameColumn('rule_single_channel', 'use_percent', 'use_type');

		$this->renameColumn('rule_single_channel', 'percent', 'use_percent');
		$this->addColumn('rule_single_channel', 'percent', Schema::TYPE_DOUBLE . ' DEFAULT NULL');

		$this->dropForeignKey('FK_rule_single_channel_usage_tenant_id', 'rule_single_channel');
		$this->dropColumn('rule_single_channel', 'usage_tenant_id');
	}
}
