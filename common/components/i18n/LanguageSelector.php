<?php
namespace common\components\i18n;

use Yii;
use yii\helpers\ArrayHelper;
use yii\base\BootstrapInterface;

class LanguageSelector implements BootstrapInterface
{
	const COOKIE_LIFETIME = 2592000; // 30 days

	const DIRECTION_LTR = 'ltr';
	const DIRECTION_RTL = 'rtl';

	const LANGUAGE_EN = 'en';
	const LANGUAGE_HE = 'he';

	public function bootstrap($app)
	{
		if (!$app->request instanceof \yii\console\Request) {
			$language = $app->request->cookies->getValue('language', $app->language);
			$app->language = $language;
		}
	}

	public static function getSupportedLanguages()
	{
		return [
			self::LANGUAGE_EN => Yii::t('common.common', 'English'),
			self::LANGUAGE_HE => Yii::t('common.common', 'Hebrew'),
		];
	}

	public static function getAliasSupportedLanguage($value)
	{
		return ArrayHelper::getValue(self::getSupportedLanguages(), $value);
	}

	public static function getListLanguageDirections()
	{
		return [
			self::LANGUAGE_HE => self::DIRECTION_RTL,
		];
	}

	public static function getAliasLanguageDirection()
	{
		return ArrayHelper::getValue(self::getListLanguageDirections(), Yii::$app->language, self::DIRECTION_LTR);
	}
}
