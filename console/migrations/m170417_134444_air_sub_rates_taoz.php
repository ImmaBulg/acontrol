<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m170417_134444_air_sub_rates_taoz extends Migration
{
    public $tableName = 'sub_air_rates_taoz';
	public function safeUp()
	{
        $this->createTable($this->tableName,[
            'id' => $this->integer(),
            'type' => $this->string(255),
            'week_part'=> $this->string(255),
            'hours_from' => $this->time(),
            'hours_to' => $this->time(),
        ]);
        $this->addPrimaryKey('pk_sart',$this->tableName,'id');
        $this->addForeignKey('fk_sart_sar',$this->tableName,'id','sub_air_rates','id','CASCADE','CASCADE');
	}

	public function safeDown()
	{
	    $this->dropForeignKey('fk_sart_sar',$this->tableName);
	    $this->dropTable($this->tableName);
	}
}
