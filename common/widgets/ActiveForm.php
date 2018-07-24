<?php

namespace common\widgets;

use common\helpers\Html;

/**
 * Class ActiveForm
 * @package common\widgets
 * @method ActiveField field($model, $attribute, $options = [])
 */
class ActiveForm extends \yii\bootstrap\ActiveForm
{
    public $options = [
        'novalidate' => 'novalidate',
        'autocomplete' => 'off',
        'role' => 'form',
    ];
    public $fieldClass = 'common\widgets\ActiveField';

    public $enableClientScript = true;
    public $enableClientValidation = true;
    public $enableAjaxValidation = false;
    public $enableOneProcessSubmit = false;
    public $encodeErrorSummary = false;

    public $validateOnChange = true;
    public $validateOnSubmit = true;
    public $validateOnBlur = false;
    public $validateOnType = false;
    public $validationDelay = 500;


    /**
     * @inheritdoc
     */
    public function run() {
        parent::run();
        if($this->enableOneProcessSubmit) {
            $id = $this->options['id'];
            $view = $this->getView();
            $view->registerJs('jQuery("#' . $id .
                              '").on("beforeSubmit", function(){$(this).find(":submit").attr("disabled", true);});');
        }
    }


    /**
     * @inheritdoc
     */
    public function errorSummary($models, $options = []) {
        Html::addCssClass($options, $this->errorSummaryCssClass);
        $options['encode'] = $this->encodeErrorSummary;
        return Html::errorSummary($models, $options);
    }


}