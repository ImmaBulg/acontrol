<?php

namespace backend\models\forms;

use \DateTime;
use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\helpers\Html;
use common\components\i18n\Formatter;

/**
 * FormMeterRawDataFilter is the class for meter raw data filter.
 */
class FormMeterRawDataFilter extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	const PERIOD_CURRENT_MONTH = 1;
	const PERIOD_CURRENT_LAST_2_MONTHS = 2;
	const PERIOD_CURRENT_CUSTOM = 3;

	const GO_BACK_SOURCE_SITE_REPORT = 'site-report';
	const GO_BACK_SOURCE_TENANT_REPORT = 'tenant-report';

	public $period;
	public $from_date;
	public $to_date;

	public $go_back_source;
	public $go_back_url;

	public function rules()
	{
		return [
			[['go_back_url'], 'filter', 'filter' => 'strip_tags'],
			[['from_date', 'to_date', 'go_back_url'], 'filter', 'filter' => 'trim'],
			[['from_date', 'to_date'], 'required'],
			[['go_back_url'], 'string'],
			[['from_date', 'to_date'], 'date', 'format' => Formatter::PHP_DATE_TIME_FORMAT],
			['to_date', '\common\components\validators\DateTimeCompareValidator', 'compareAttribute' => 'from_date', 'format' => Formatter::PHP_DATE_TIME_FORMAT, 'operator' => '>='],
			['period', 'in', 'range' => array_keys(self::getListPeriods()), 'skipOnEmpty' => true],
			['go_back_source', 'in', 'range' => array_keys(self::getListGoBackSources()), 'skipOnEmpty' => true],
		];
	}

	public function attributeLabels()
	{
		return [
			'period' => Yii::t('backend.meter', 'Show data for period'),
			'from_date' => Yii::t('backend.meter', 'From'),
			'to_date' => Yii::t('backend.meter', 'To'),
		];
	}

	public static function getListPeriods()
	{
		return [
			self::PERIOD_CURRENT_MONTH => Yii::t('backend.meter', 'Current month'),
			self::PERIOD_CURRENT_LAST_2_MONTHS => Yii::t('backend.meter', 'Last 2 months'),
			self::PERIOD_CURRENT_CUSTOM => Yii::t('backend.meter', 'Custom'),
		];
	}

    public static function getListPeriodAttributes()
    {
        return [
            self::PERIOD_CURRENT_MONTH => [
                'data-period' => [
                    'from_date' => Yii::$app->formatter->asDatetime((new DateTime('first day of this month'))->modify('midnight')),
                    'to_date' => Yii::$app->formatter->asDatetime((new DateTime('last day of this month'))->modify('midnight')),
                ],
            ],
            self::PERIOD_CURRENT_LAST_2_MONTHS => [
                'data-period' => [
                    'from_date' => Yii::$app->formatter->asDatetime((new DateTime('first day of last month'))->modify('midnight')),
                    'to_date' => Yii::$app->formatter->asDatetime((new DateTime('last day of this month'))->modify('midnight')),
                ],
            ],
            self::PERIOD_CURRENT_CUSTOM => [
                'data-period' => [
                    'from_date' => null,
                    'to_date' => null,
                ],
            ],
        ];
    }

	public static function getListGoBackSources()
	{
		return [
			self::GO_BACK_SOURCE_SITE_REPORT => Yii::t('backend.meter', 'Go back to issue bill'),
			self::GO_BACK_SOURCE_TENANT_REPORT => Yii::t('backend.meter', 'Go back to issue bill'),
		];
	}

	public function getAliasGoBackSource()
	{
		$list = self::getListGoBackSources();
		return (isset($list[$this->go_back_source])) ? $list[$this->go_back_source] : '';
	}

	public static function getListGoToSources()
	{
		return [
			self::GO_BACK_SOURCE_SITE_REPORT => Yii::t('backend.meter', 'Go to Channel raw data management'),
			self::GO_BACK_SOURCE_TENANT_REPORT => Yii::t('backend.meter', 'Go to Channel raw data management'),
		];		
	}

	public function getAliasGoToSource()
	{
		$list = self::getListGoToSources();
		return (isset($list[$this->go_back_source])) ? $list[$this->go_back_source] : '';
	}

	public function loadDefaultAttributes()
	{
		$this->from_date = Yii::$app->formatter->asDateTime((new DateTime('today midnight')));
		$this->to_date = Yii::$app->formatter->asDateTime((new DateTime('tomorrow midnight')));
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$period = self::PERIOD_CURRENT_CUSTOM;
		$list = self::getListPeriodAttributes();

		foreach ($list as $key => $values) {
			if ($this->from_date == $values['data-period']['from_date'] && $this->to_date == $values['data-period']['to_date']) {
				$period = $key;
				break;
			}
		}

		$this->period = $period;
		return true;
	}

	public static function getGoToUrl($meter_id, $channel_id, $attributes = [], $options = [])
	{
		$link = '';
		$form = new self();
		$form->attributes = $attributes;
		$go_to_source = $form->getAliasGoToSource();

		if ($go_to_source != null) {
			$form->period = self::PERIOD_CURRENT_CUSTOM;
			$go_to_url = ArrayHelper::merge(['/meter-raw-data/list', 'meter_id' => $meter_id, 'channel_id' => $channel_id], [(new \ReflectionClass(self::className()))->getShortName() => $form->attributes]);
			$link = Html::a($go_to_source, $go_to_url, $options);
		}
		
		return $link;
	}

	public static function getGoBackLink($options = [])
	{
		$link = '';
		$form = new self();

		if ($form->load(Yii::$app->request->get())) {
			$go_back_url = $form->go_back_url;
			$go_back_source = $form->getAliasGoBackSource();

			if ($go_back_source != null) {
				$link = Html::a($go_back_source, $go_back_url, $options);
			}
		}

		return $link;
	}
}
