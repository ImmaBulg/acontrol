<?php
use common\helpers\Html;
use common\widgets\ActiveForm;
use backend\models\forms\FormSearch;
?>
<?php $form_active = ActiveForm::begin([
	'id' => 'form-search',
	'action' => ['/dashboard/search', 'type' => FormSearch::CLIENTS],
	'options' => $options,
	'method' => 'GET',
]); ?>
	<fieldset>
        <div class="form-group has-feedback">
            <?=Html::textInput('q',$q,['class'=>'form-control typeahead rounded','placeholder'=>'']) ?>
            <button type="submit" class="btn btn-theme fa fa-search form-control-feedback rounded "></button>
        </div>
	</fieldset>
<?php ActiveForm::end(); ?>

