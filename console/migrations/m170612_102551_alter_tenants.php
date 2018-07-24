<?php

use yii\db\Schema;

class m170612_102551_alter_tenants extends \common\components\db\Migration
{
    public $tableName = 'tenant';
	public function up()
	{
	    $this->execute("update tenant set option_visible_barcode = 0");
        $this->alterColumn($this->tableName,'option_visible_barcode',$this->boolean()->defaultValue(0)->notNull());
	}

	public function down()
	{
        $this->alterColumn($this->tableName,'option_visible_barcode',$this->boolean()->defaultValue(1)->notNull());
        $this->execute("update tenant set option_visible_barcode = 1");
	}
}
