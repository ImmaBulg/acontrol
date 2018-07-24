<?php
use yii\db\Schema;

class m170505_143536_alter_meter_types extends \common\components\db\Migration
{
    public $tableName = 'meter_type';


    public function safeUp() {
        $this->addColumn($this->tableName, 'is_divide_by_1000', $this->boolean()->defaultValue(0)->notNull());
    }


    public function safeDown() {
        $this->dropColumn($this->tableName, 'is_divide_by_1000');
    }
}
