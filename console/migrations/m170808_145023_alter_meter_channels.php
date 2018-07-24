<?php

use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m170808_145023_alter_meter_channels extends Migration
{
    public $tableName = 'meter_channel';


    public function safeUp() {
        $this->addColumn($this->tableName, 'is_main', $this->boolean()->defaultValue(0)->notNull());
    }


    public function safeDown() {
        $this->dropColumn($this->tableName, 'is_main');
    }
}
