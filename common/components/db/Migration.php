<?php

namespace common\components\db;

use Yii;


/**
 * Migration is the base class for representing a database migration.
 *
 * Migration is designed to be used together with the "yii migrate" command.
 *
 * Each child class of Migration represents an individual database migration which
 * is identified by the child class name.
 *
 * Within each migration, the [[up()]] method should be overridden to contain the logic
 * for "upgrading" the database; while the [[down()]] method for the "downgrading"
 * logic. The "yii migrate" command manages all available migrations in an application.
 *
 * If the database supports transactions, you may also override [[safeUp()]] and
 * [[safeDown()]] so that if anything wrong happens during the upgrading or downgrading,
 * the whole migration can be reverted in a whole.
 *
 * Migration provides a set of convenient methods for manipulating database data and schema.
 * For example, the [[insert()]] method can be used to easily insert a row of data into
 * a database table; the [[createTable()]] method can be used to create a database table.
 * Compared with the same methods in [[Command]], these methods will display extra
 * information showing the method parameters and execution time, which may be useful when
 * applying migrations.
 */
class Migration extends \yii\db\Migration
{
	public $dbOptions;

	public function init()
	{
		parent::init();
		
		$this->dbOptions = $this->generateDbOptions();
	}

	public function generateDbOptions()
	{
		$list = self::getDbOptionsList();
		$driverName = $this->db->driverName;

		return isset($list[$driverName]) ? $list[$driverName] : null; 
	}

	public static function getDbOptionsList()
	{
		return [
			'mysql' => 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB',
		];
	}
}