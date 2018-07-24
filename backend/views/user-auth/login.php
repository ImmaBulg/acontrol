<?php
use backend\assets\admin\page\SignType2Asset;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form_active yii\bootstrap\ActiveForm */
/* @var $form \common\models\LoginForm */
SignType2Asset::register($this);
$this->title = 'Login';
?>
<div id="sign-wrapper">

    <!-- Brand -->
    <div class="brand">
        <img src="/images/logo.png">
    </div>
    <!--/ Brand -->


    <!-- Login form -->

    <div class="sign-header">
        <div class="form-group">
            <div class="sign-text">
                <span>Member Area</span>
            </div>
        </div><!-- /.form-group -->
    </div><!-- /.sign-header -->
    <?php
    $form_active = ActiveForm::begin([
                                         'id' => 'login-form',
                                         'enableAjaxValidation' => false,
                                         'enableClientValidation' => true,
                                         'action' => ['login'],
                                         'options' => [
                                             'class' => 'sign-in form-horizontal shadow rounded no-overflow',
                                             'data-pjax' => true
                                         ]
                                     ]);
    ?>
    <div class="sign-body">

        <div class="form-group">
            <div class="input-group input-group-lg rounded no-overflow"  style=" background-color: #fff;">
                <?= $form_active->field($form, 'nickname')->textInput(['class' => 'form-control input-sm', 'style' => 'padding-top: 10px; padding-bottom: 10px; margin-top:10px; margin-bottom: -5px;', 'placeholder' => 'Username'])->label(false)?>
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
            </div>
        </div><!-- /.form-group -->
        <div class="form-group">
            <div class="input-group input-group-lg rounded no-overflow" style=" background-color: #fff;">
                <?= $form_active->field($form, 'password')->passwordInput(['class' => 'form-control input-sm', 'style' => 'padding-top: 10px;  padding-bottom: 10px;margin-top:10px; margin-bottom: -5px;', 'placeholder' => 'Password'])->label(false)?>
                <span class="input-group-addon"><i class="fa fa-lock"></i></span>

            </div>
        </div><!-- /.form-group -->
        <div class="form-group text-right">
        <a href="<?= Yii::$app->getUrlManager()->createUrl('site/lostpassword') ?>" title="lost password">Lost password?</a>
        </div>
    </div><!-- /.sign-body -->
    <div class="sign-footer">
        <div class="form-group">
            <div class="row">
                <div class="col-xs-12 col-md-12">
                    <div class="ckbox ckbox-theme">
                        <?= $form_active->field($form, 'rememberMe',['options'=>[
                            ['class'=>'form-group no-padding']
                        ]])->checkbox() ?>
                    </div>
                </div>

            </div>
        </div><!-- /.form-group -->
        <div class="form-group">
            <?= Html::submitButton('Login', ['class' => 'btn btn-theme btn-lg btn-block no-margin rounded', 'name' => 'login-button']) ?>

        </div><!-- /.form-group -->

    </div><!-- /.sign-footer -->
    <?php
    ActiveForm::end();?>

</div><!-- /#sign-wrapper -->

