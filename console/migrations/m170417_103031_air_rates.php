<?php
use dezmont765\yii2bundle\db\Migration;

class m170417_103031_air_rates extends Migration
{
    public $tableName = 'air_rates';


    public function safeUp() {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'rate_type_id' => $this->integer(),
            'fixed_payment' => $this->float(),
            'season' => $this->tinyint(),
            'start_date' => $this->timestamp(),
            'end_date' => $this->timestamp(),
            'status' => $this->tinyint(),
            'create_at' => $this->timestamp(),
            'modified_at' => $this->timestamp(),
            'created_by' => $this->integer(),
            'modified_by' => $this->integer(),
        ]);
        $this->addForeignKey('fk_air_rates_type', $this->tableName, 'rate_type_id', 'rate_type', 'id', 'CASCADE',
                             'CASCADE');
        $this->addForeignKey('fk_air_rates_creator', $this->tableName, 'created_by', 'user', 'id', 'CASCADE',
                             'CASCADE');
        $this->addForeignKey('fk_air_rates_editor', $this->tableName, 'modified_by', 'user', 'id', 'CASCADE',
                             'CASCADE');
    }


    public function safeDown() {
        $this->dropForeignKey('fk_air_rates_type', $this->tableName);
        $this->dropForeignKey('fk_air_rates_creator', $this->tableName);
        $this->dropForeignKey('fk_air_rates_editor', $this->tableName);
        $this->dropTable($this->tableName);
    }
}
