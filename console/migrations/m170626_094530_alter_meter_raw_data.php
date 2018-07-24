<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m170626_094530_alter_meter_raw_data extends Migration
{
    public $tableName = 'meter_raw_data';
	public function safeUp()
	{
        $this->addColumn($this->tableName,'is_main',$this->boolean()->defaultValue(0)->notNull());
	}

	public function safeDown()
	{
		$this->dropColumn($this->tableName,'is_main');
	}
}
