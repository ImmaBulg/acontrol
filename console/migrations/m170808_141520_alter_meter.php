<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m170808_141520_alter_meter extends Migration
{
    public $tableName = 'meter';
	public function safeUp()
	{
        $this->addColumn($this->tableName,'is_main',$this->boolean());
	}

	public function safeDown()
	{
		$this->dropColumn($this->tableName,'is_main');
	}
}
