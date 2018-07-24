<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m170424_102624_alter_air_raw_data extends Migration
{
    private $_table = 'air_meter_raw_data';
	public function safeUp()
	{
        $this->dropForeignKey('air_meter_raw_to_meter', $this->_table);
        $this->alterColumn($this->_table, 'meter_id', $this->string()->notNull());
        $this->createIndex('meter_id', $this->_table, 'meter_id');
	}

	public function safeDown()
	{
        $this->dropIndex('meter_id', $this->_table);
        $this->alterColumn($this->_table, 'meter_id', $this->integer()->notNull());
        $this->addForeignKey('air_meter_raw_to_meter', $this->_table, 'meter_id', 'meter', 'id');
    }
}