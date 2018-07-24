<?php

namespace backend\assets\admin\form;

use yii\web\AssetBundle;

/**
 * @author Djava UI <support@djavaui.com>
 */
class PageLevelXeditableAsset extends AssetBundle {

    public $sourcePath = '@bower';
    public $css = [
        'fontawesome/css/font-awesome.min.css',
        'animate.css/animate.min.css',
        'bootstrap-wysihtml5/src/bootstrap-wysihtml5.css',
        'summernote/dist/summernote.css',
        'select2-ng/select2.css',
        'select2-ng/select2-bootstrap.css',
        'smalot-bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css',
        'x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css',
        'x-editable/dist/inputs-ext/typeaheadjs/lib/typeahead.js-bootstrap.css',
        'x-editable/dist/inputs-ext/address/address.css'
    ];
    public $js = [
        'jquery-mockjax/jquery.mockjax.js',
        'moment/min/moment.min.js',
        'select2-ng/select2.min.js',
        'smalot-bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js',
        'x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js',
        'x-editable/dist/inputs-ext/typeaheadjs/lib/typeahead.js',
        'x-editable/dist/inputs-ext/typeaheadjs/typeaheadjs.js',
        'x-editable/dist/inputs-ext/address/address.js'
    ];

}
