<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m170718_145724_alter_sites extends Migration
{
    public $tableName = 'site_billing_setting';


    public function safeUp() {
        $this->addColumn($this->tableName, 'irregular_hours_from', $this->time());
        $this->addColumn($this->tableName, 'irregular_hours_to', $this->time());
        $this->addColumn($this->tableName, 'irregular_additional_percent', $this->float());
    }


    public function safeDown() {
        $this->dropColumn($this->tableName, 'irregular_hours_from');
        $this->dropColumn($this->tableName, 'irregular_hours_to');
        $this->dropColumn($this->tableName, 'irregular_additional_percent');
    }
}
