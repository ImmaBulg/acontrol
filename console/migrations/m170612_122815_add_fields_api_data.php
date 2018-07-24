<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m170612_122815_add_fields_api_data extends Migration
{
    private $_table = 'air_meter_raw_data';
	public function safeUp()
	{
        $this->addColumn($this->_table, 'cop', $this->float());
        $this->addColumn($this->_table, 'delta_t', $this->float());
	}

	public function safeDown()
	{
        $this->dropColumn($this->_table, 'cop');
        $this->dropColumn($this->_table, 'delta_t');
	}
}
