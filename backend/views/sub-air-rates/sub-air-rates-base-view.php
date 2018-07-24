<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 28.04.2017
 * Time: 19:42
 * @var $models [] \common\models\AIncentiveActions
 */
use dezmont765\yii2bundle\widgets\PartialActiveForm;

$form = PartialActiveForm::begin() ?>
<?php foreach($models as $key => $model) : ?>
    <?= $this->render($view, ['form' => $form, 'model' => $model, 'key' => $key]) ?>
<?php endforeach; ?>
<div id="child-place-position">
    <?php if($model instanceof \common\models\SubAirRatesTaoz) : ?>
    <div class="form-group">
        <button id="add-child-condition" class="btn btn-success">Add condition</button>
    </div>
      <?php endif?>
</div>

<?php PartialActiveForm::end() ?>

