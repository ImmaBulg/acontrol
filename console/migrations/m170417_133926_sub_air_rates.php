<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m170417_133926_sub_air_rates extends Migration
{
    public $tableName = 'sub_air_rates';
	public function safeUp()
	{
        $this->createTable($this->tableName,[
           'id' => $this->primaryKey(),
           'rate_id' => $this->integer(),
           'category' => $this->string(255),
           'rate' => $this->float(),
           'identifier' => $this->string(255),
        ]);
        $this->addForeignKey('fk_sar_air_rates',$this->tableName,'rate_id','air_rates','id','CASCADE','CASCADE');
	}

	public function safeDown()
	{
        $this->dropForeignKey('fk_sar_air_rates',$this->tableName);
        $this->dropTable($this->tableName);
	}
}
