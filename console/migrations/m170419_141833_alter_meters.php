<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m170419_141833_alter_meters extends Migration
{
    public $tableName = 'meter';


    public function safeUp() {
        $this->addColumn($this->tableName, 'type',$this->string(255));
        $this->execute("UPDATE meter set type = \"" . \common\models\Meter::TYPE_ELECTRICITY . "\" ");
    }


    public function safeDown() {
        $this->dropColumn($this->tableName,'type');
    }
}
