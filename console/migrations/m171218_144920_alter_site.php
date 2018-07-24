<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m171218_144920_alter_site extends Migration
{
    public $tableName = 'site';
	public function up()
	{
        $this->addColumn($this->tableName, 'manual_cop_pisga', $this->float());
        $this->addColumn($this->tableName, 'manual_cop_geva', $this->float());
        $this->addColumn($this->tableName, 'manual_cop_shefel', $this->float());
	}

	public function down()
	{
		$this->dropColumn($this->tableName,'manual_cop_pisga');
		$this->dropColumn($this->tableName,'manual_cop_geva');
		$this->dropColumn($this->tableName,'manual_cop_shefel');
	}
}
