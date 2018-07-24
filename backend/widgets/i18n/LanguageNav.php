<?php
namespace backend\widgets\i18n;

use Yii;
use common\helpers\Html;
use common\components\i18n\LanguageSelector;

class LanguageNav extends \yii\base\Widget
{
	public $options = [];

	public function init()
	{
		parent::init();
	}

	public function run()
	{
		$items = [];
		$languages = LanguageSelector::getSupportedLanguages();

		if ($languages != null) {
			foreach ($languages as $code => $name) {
				if ($code == Yii::$app->language) {
					$items['label'] = $name;
				} else {
					$items['items'][] = [
						'label' => $name,
						'url' => ['/language/switch', 'code' => $code],
					];
				}
			}
		}

		return $this->render('language_nav', [
			'options' => $this->options,
			'items' => $items,
		]);
	}
}
