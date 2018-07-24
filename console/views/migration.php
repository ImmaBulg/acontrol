<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name */

echo "<?php\n";
?>
use dezmont765\yii2bundle\db\Migration;
use yii\db\Schema;

class <?= $className ?> extends Migration
{
    public $tableName = '';
	public function safeUp()
	{

	}

	public function safeDown()
	{
		
	}
}
