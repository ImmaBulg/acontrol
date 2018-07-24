<?php
use yii\db\Schema;

class m170417_081644_create_soler_cost extends \common\components\db\Migration
{
    public $tableName = 'soler_cost';


    public function safeUp() {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'from_date' => $this->date(),
            'to_date' => $this->date(),
            'cost' => $this->float()
        ]);
    }


    public function safeDown() {
        $this->dropTable($this->tableName);
    }
}
