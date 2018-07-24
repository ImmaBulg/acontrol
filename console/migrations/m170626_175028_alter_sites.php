<?php

use yii\db\Schema;

class m170626_175028_alter_sites extends \common\components\db\Migration
{
    public $tableName = 'site';
	public function up()
	{
        $this->addColumn($this->tableName,'power_factor_visibility',$this->smallInteger()->notNull()->defaultValue(1));
	}

	public function down()
	{
		$this->dropColumn($this->tableName,'power_factor_visibility');
	}
}
