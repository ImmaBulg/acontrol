<?php
use common\helpers\Html;
use yii\bootstrap\Nav;

echo Nav::widget([
	'options' => $options,
	'items' => [$items],
]);
