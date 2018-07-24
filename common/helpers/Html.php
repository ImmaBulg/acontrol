<?php

namespace common\helpers;

use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\bootstrap\BootstrapPluginAsset;

use common\components\i18n\Formatter;

class Html extends \yii\helpers\Html
{
	public static function errorSummary($models, $options = [])
	{
		$header = isset($options['header']) ? $options['header'] : '<p>' . Yii::t('common.common', 'Please fix the following errors:') . '</p>';
		$footer = isset($options['footer']) ? $options['footer'] : '';
		$encode = !isset($options['encode']) || $options['encode'] !== false;
		unset($options['header'], $options['footer'], $options['encode']);

		$lines = [];
		if (!is_array($models)) {
			$models = [$models];
		}
		foreach ($models as $model) {
			/* @var $model Model */
			foreach ($model->getFirstErrors() as $error) {
				$lines[] = $encode ? Html::encode($error) : $error;
			}
		}

		if (empty($lines)) {
			// still render the placeholder for client-side validation use
			$content = "<ul></ul>";
			$options['style'] = isset($options['style']) ? rtrim($options['style'], ';') . '; display:none' : 'display:none';
		} else {
			$content = "<ul><li>" . implode("</li>\n<li>", $lines) . "</li></ul>";
		}
		$options['class'] = (isset($options['class'])) ? $options['class']. ' alert alert-dismissible alert-danger fade in' : 'alert alert-dismissible alert-danger fade in';
		$content = '<button type="button" class="close" data-dismiss="alert" aria-label="' .Yii::t('common.common', 'Close'). '"><span aria-hidden="true">×</span></button>' . $header . $content . $footer;
		return Html::tag('div', $content, $options);
	}
	
	public static function a($text, $url = null, $options = [])
	{
		if ($url !== null) {
			$options['href'] = Url::to($url);
		}

		if (isset($options['data']['toggle'])) {
			$id = $options['id'] = isset($options['id']) ? $options['id'] : 'multiselect_' .uniqid();

			switch ($options['data']['toggle']) {
				case 'confirm':
					$jsOptions = [
						'text' => (isset($options['data']['confirm-text'])) ? $options['data']['confirm-text'] : Yii::t('common.common', 'Are you sure?'),
						'confirmButton' => (isset($options['data']['confirm-button'])) ? $options['data']['confirm-button'] : Yii::t('common.common', 'Confirm'),
						'cancelButton' => (isset($options['data']['cancel-button'])) ? $options['data']['cancel-button'] : Yii::t('common.common', 'Cancel'),
						'post' => (isset($options['data']['confirm-post'])) ? $options['data']['confirm-post'] : false,
						'confirmButtonClass' => (isset($options['data']['confirm-button-class'])) ? $options['data']['confirm-button-class'] : 'btn btn-primary',
						'cancelButtonClass' => 'btn btn-link',
						'dialogClass' => 'modal-dialog',
					];
					$options['class'] = isset($options['class']) ? $options['class']. ' js-confirm' : 'js-confirm';
					Yii::$app->view->registerJsFile('@web/js/plugins/confirm.js');
					Yii::$app->view->registerJs("jQuery('#$id').confirm(" .Json::encode($jsOptions). ");");
					break;

				case 'modal':
					if ($options['href'] != '#') {
						$options['data']['target'] = (!empty($options['data']['target'])) ? $options['data']['target'] : '#modal-dialog-'. md5(rand());
						$options['class'] = isset($options['class']) ? $options['class']. ' js-modal-link' : 'js-modal-link';
						
						BootstrapPluginAsset::register(Yii::$app->view);
						Yii::$app->view->registerJs("jQuery('#$id').on('click', function(e){e.preventDefault();var url = jQuery(this).attr('href');var id = jQuery(this).data('target');if(!jQuery(id).length){if(url.indexOf('#') != 0){jQuery.get(url, function(data){	jQuery('<div id=\"' +id.replace('#','')+ '\" class=\"modal fade\">' +data+ '</div>').modal();}).success(function(){});}}});");	
					}
					break;

				default:
					break;
			}
		}

		return static::tag('a', $text, $options);
	}

	public static function activeDateInput($model, $attribute, $options = [], $pluginOptions = [])
	{
		$name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
		$value = isset($options['value']) ? $options['value'] : static::getAttributeValue($model, $attribute);
		
		if (!array_key_exists('id', $options)) {
			$options['id'] = static::getInputId($model, $attribute);
		}

		return static::dateInput($name, $value, $options, $pluginOptions);
	}

	public static function dateInput($name, $value = null, $options = [], $pluginOptions = [])
	{
		if ($value != null && Yii::$app->formatter->normalizeDatetimeValue($value) != null) {
			$value = Yii::$app->formatter->asDate($value, Formatter::PHP_DATE_FORMAT);
		}

		$options['id'] = isset($options['id']) ? $options['id'] : 'dateinput_' .uniqid();
		$input_id = $options['id'];

		$jsOptions = [
			'format' => Formatter::JS_DATE_FORMAT,
			'first_day' => Formatter::JS_WEEK_START,
			'prev' =>  Yii::t('common.common', 'Prev'),
			'next' =>  Yii::t('common.common', 'Next'),
			'hide_on_select' => true,
			'locale' => [
				'days' => [
					Yii::t('common.common', 'Sunday'),
					Yii::t('common.common', 'Monday'),
					Yii::t('common.common', 'Tuesday'),
					Yii::t('common.common', 'Wednesday'),
					Yii::t('common.common', 'Thursday'),
					Yii::t('common.common', 'Friday'),
					Yii::t('common.common', 'Saturday'),
				],
				'daysShort' => [
					Yii::t('common.common', 'Sun'),
					Yii::t('common.common', 'Mon'),
					Yii::t('common.common', 'Tue'),
					Yii::t('common.common', 'Wed'),
					Yii::t('common.common', 'Thu'),
					Yii::t('common.common', 'Fri'),
					Yii::t('common.common', 'Sat'),
				],
				'daysMin' => [
					Yii::t('common.common', 'Su'),
					Yii::t('common.common', 'Mo'),
					Yii::t('common.common', 'Tu'),
					Yii::t('common.common', 'We'),
					Yii::t('common.common', 'Th'),
					Yii::t('common.common', 'Fr'),
					Yii::t('common.common', 'Sa'),
				],
				'months' => [
					Yii::t('common.common', 'January'),
					Yii::t('common.common', 'February'),
					Yii::t('common.common', 'March'),
					Yii::t('common.common', 'April'),
					Yii::t('common.common', 'May'),
					Yii::t('common.common', 'June'),
					Yii::t('common.common', 'July'),
					Yii::t('common.common', 'August'),
					Yii::t('common.common', 'September'),
					Yii::t('common.common', 'October'),
					Yii::t('common.common', 'November'),
					Yii::t('common.common', 'December'),
				],
				'monthsShort' => [
					Yii::t('common.common', 'Jan'),
					Yii::t('common.common', 'Feb'),
					Yii::t('common.common', 'Mar'),
					Yii::t('common.common', 'Apr'),
					Yii::t('common.common', 'May'),
					Yii::t('common.common', 'Jun'),
					Yii::t('common.common', 'Jul'),
					Yii::t('common.common', 'Aug'),
					Yii::t('common.common', 'Sep'),
					Yii::t('common.common', 'Oct'),
					Yii::t('common.common', 'Nov'),
					Yii::t('common.common', 'Dec'),
				],
			],
		];

		if ($pluginOptions != null) {
			$jsOptions  = array_merge($jsOptions, $pluginOptions);
		}

		Yii::$app->view->registerCssFile('@web/css/plugins/pickmeup.css');
		Yii::$app->view->registerJsFile('@web/js/plugins/pickmeup.js');
		Yii::$app->view->registerJs("jQuery('#$input_id').pickmeup(" .Json::encode($jsOptions). ");");
		return static::input('text', $name, $value, $options);
	}

	const TYPE_NUMBER = 'number';

    public static function input($type, $name = null, $value = null, $options = [])
    {
        if (!isset($options['type'])) {
            $options['type'] = $type;
        }

        if (!empty($options['allow_only']) && $options['type'] == 'text') {
			$input_id = $options['id'];
			switch ($options['allow_only']) {
				case self::TYPE_NUMBER:
					Yii::$app->view->registerJs("jQuery('#$input_id').on('keydown', function(e){-1!==$.inArray(e.keyCode,[109,46,8,9,27,13,110,190])||/65|67|86|88/.test(e.keyCode)&&(!0===e.ctrlKey||!0===e.metaKey)||35<=e.keyCode&&40>=e.keyCode||(e.shiftKey||48>e.keyCode||57<e.keyCode)&&(96>e.keyCode||105<e.keyCode)&&e.preventDefault()});");
					break;
				
				default:
					break;
			}
			unset($options['allow_only']);    	
        }

        $options['name'] = $name;
        $options['value'] = $value === null ? null : (string) $value;
        return static::tag('input', '', $options);
    }

	public static function activeMultiSelect2Side($model, $attribute, $items, $options = [], $pluginOptions = [])
	{
		$name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);

		if (substr_compare($name, '[]', -2, 2)) {
			$name .= '[]';
		}
		
		$selection = static::getAttributeValue($model, $attribute);

		if (!array_key_exists('unselect', $options)) {
			$options['unselect'] = '';
		}
		if (!array_key_exists('id', $options)) {
			$options['id'] = static::getInputId($model, $attribute);
		}

		return static::multiSelect2Side($name, $selection, $items, $options, $pluginOptions);
	}

	public static function multiSelect2Side($name, $selection = null, $items = [], $options = [], $pluginOptions = [])
	{
		$options['id'] = isset($options['id']) ? $options['id'] : 'multiselect_' .uniqid();
		$input_id = $options['id'];
		$options['multiple'] = 'multiple';
		$options['name'] = $name;

		if (isset($options['unselect'])) {
			// add a hidden field so that if the list box has no option being selected, it still submits a value
			if (!empty($name) && substr_compare($name, '[]', -2, 2) === 0) {
				$name = substr($name, 0, -2);
			}
			$hidden = static::hiddenInput($name, $options['unselect']);
			unset($options['unselect']);
		} else {
			$hidden = '';
		}

		$selectOptions = static::renderSelectOptions($selection, $items, $options);;
		$jsOptions = [];

		if ($pluginOptions != null) {
			$jsOptions  = array_merge($jsOptions, $pluginOptions);
		}

		Yii::$app->view->registerCssFile('@web/css/plugins/multiselect2side.css');
		Yii::$app->view->registerJsFile('@web/js/plugins/multiselect2side.js');
		Yii::$app->view->registerJs("jQuery('#$input_id').multiselect2side(" .Json::encode($jsOptions). ");");
		return static::tag('select', "\n" . $selectOptions . "\n", $options);
	}
}
