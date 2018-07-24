<?php

use yii\db\Schema;

class m170524_163304_alter_table_tenant extends \common\components\db\Migration
{
    public $tableName = 'tenant';


    public function up()
    {
        $length = 255;

        $this->addColumn($this->tableName, 'prefix', $this->string($length)  . ' DEFAULT NULL');
        $this->addColumn($this->tableName, 'ending', $this->string($length)  . ' DEFAULT NULL');
        $this->addColumn($this->tableName, 'client_code', $this->string($length)  . ' DEFAULT NULL');
        $this->addColumn($this->tableName, 'contract_id', $this->string($length)  . ' DEFAULT NULL');
        $this->addColumn($this->tableName, 'property_id', $this->string($length)  . ' DEFAULT NULL');
        $this->addColumn($this->tableName, 'formatting', $this->string($length)  . ' DEFAULT NULL');
        $this->addColumn($this->tableName, 'option_visible_barcode', $this->boolean()->defaultValue(1)->notNull());
    }

    public function down()
    {
        $this->dropColumn($this->tableName, 'prefix');
        $this->dropColumn($this->tableName, 'ending');
        $this->dropColumn($this->tableName, 'client_code');
        $this->dropColumn($this->tableName, 'contract_id');
        $this->dropColumn($this->tableName, 'property_id');
        $this->dropColumn($this->tableName, 'formatting');
        $this->dropColumn($this->tableName, 'option_visible_barcode');
    }
}
