<?php
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class m170421_093206_create_air_meter_raw_data extends Migration
{
    private $_table = 'air_meter_raw_data';
	public function safeUp()
	{
        $table_options = null;
        if ($this->db->driverName === 'mysql') {
            $table_options = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this->_table, [
            'id' => $this->primaryKey()->notNull(),
            'kilowatt_hour' => $this->float(),
            'cubic_meter' => $this->float(),
            'kilowatt' => $this->float(),
            'cubic_meter_hour' => $this->float(),
            'incoming_temp' => $this->float(),
            'outgoing_temp' => $this->float(),
            'meter_id' => $this->integer(),
            'channel_id' => $this->integer(),
            'site_id' => $this->integer(),
            'created_by' => $this->integer(),
            'modified_by' => $this->integer(),
            'status' => $this->smallInteger()->defaultValue(0)->notNull(),
            'datetime' => $this->timestamp(),
            'created_at' => $this->timestamp(),
            'modified_at' => $this->timestamp()
        ], $table_options);

        $this->addForeignKey('air_meter_raw_to_meter', $this->_table, 'meter_id', 'meter', 'id');
        $this->addForeignKey('air_meter_raw_to_created', $this->_table, 'created_by', 'user', 'id');
        $this->addForeignKey('air_meter_raw_to_updated', $this->_table, 'modified_by', 'user', 'id');
        $this->addForeignKey('air_meter_raw_to_site', $this->_table, 'site_id', 'site', 'id');
	}

	public function safeDown()
	{
	    $this->dropForeignKey('air_meter_raw_to_meter', $this->_table);
	    $this->dropForeignKey('air_meter_raw_to_created', $this->_table);
	    $this->dropForeignKey('air_meter_raw_to_updated', $this->_table);
	    $this->dropForeignKey('air_meter_raw_to_site', $this->_table);

		$this->dropTable($this->_table);
	}
}
