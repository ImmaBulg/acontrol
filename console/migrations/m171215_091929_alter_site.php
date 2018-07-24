<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m171215_091929_alter_site extends Migration
{
    public $tableName = 'site';
	public function Up()
	{
        $this->addColumn($this->tableName, 'manual_cop', $this->float());
	}

	public function Down()
	{
		$this->dropColumn($this->tableName, 'manual_cop');
	}
}
