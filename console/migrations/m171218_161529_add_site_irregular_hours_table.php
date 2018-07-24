<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m171218_161529_add_site_irregular_hours_table extends Migration
{
    public $tableName = 'tenant_irregular_hours';
	public function safeUp()
	{
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey()->unsigned(),
            'tenant_id' => $this->integer()->unsigned(),
            'day_number' => $this->tinyint(),
            'hours_from' => $this->time(),
            'hours_to' => $this->time()
        ]);
	}

	public function safeDown()
	{
		$this->dropTable($this->tableName);
	}
}
