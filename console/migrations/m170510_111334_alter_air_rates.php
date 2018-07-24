<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m170510_111334_alter_air_rates extends Migration
{
    public $tableName = 'air_rates';
	public function safeUp()
	{
        $this->alterColumn($this->tableName,'start_date',$this->date());
        $this->alterColumn($this->tableName,'end_date',$this->date());
	}

	public function safeDown()
	{
        $this->alterColumn($this->tableName,'start_date',$this->timestamp());
        $this->alterColumn($this->tableName,'end_date',$this->timestamp());
	}
}
