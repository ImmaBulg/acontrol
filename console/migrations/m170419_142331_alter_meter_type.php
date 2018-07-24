<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m170419_142331_alter_meter_type extends Migration
{
    public $tableName = 'meter_type';
    public function safeUp() {
        $this->addColumn($this->tableName, 'type',$this->string(255));
        $this->execute("UPDATE meter_type set type = \"" . \common\models\Meter::TYPE_ELECTRICITY . "\" ");
    }


    public function safeDown() {
        $this->dropColumn($this->tableName,'type');
    }
}
