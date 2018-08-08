<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.04.2017
 * Time: 18:25
 * @var $form PartialActiveForm
 */
use dezmont765\yii2bundle\widgets\PartialActiveForm; ?>

<?= $form->field($model, "[$key]rate")->textInput(['placeholder' => '00.00'])->label('Rate in Agorot ₪'); ?>
<?= $form->field($model, "[$key]identifier")->textInput()->label('Note'); ?>
