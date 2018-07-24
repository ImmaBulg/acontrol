<?php
namespace console\controllers;

class MigrateController extends \yii\console\controllers\MigrateController
{
    public $templateFile = '@console/views/migration.php';
    public $generatorTemplateFiles = [
        'create_table' => '@console/views/migration.php',
        'drop_table' => '@console/views/migration.php',
        'add_column' => '@console/views/migration.php',
        'drop_column' => '@console/views/migration.php',
        'create_junction' => '@console/views/migration.php'
    ];

}
