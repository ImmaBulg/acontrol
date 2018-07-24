<?php namespace common\models;

use \DateTime;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\i18n\Formatter;
use common\components\behaviors\UserIdBehavior;
use common\components\behaviors\ToTimestampBehavior;

/**
 * BaseMeterRawData is the class for the table "meter_raw_data".
 */
abstract class BaseMeterRawData extends ActiveRecord implements IMeterRawData
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;

	const SEASON_WINTER = 'winter';
	const SEASON_SPRING = 'spring';
	const SEASON_SUMMER = 'summer';
	const SEASON_FALL = 'fall';

	const CONSUMPTION_SHEFEL = 'shefel';
	const CONSUMPTION_GEVA = 'geva';
	const CONSUMPTION_PISGA = 'pisga';
	const CONSUMPTION_EXPORT_SHEFEL = 'export_shefel';
	const CONSUMPTION_EXPORT_GEVA = 'export_geva';
	const CONSUMPTION_EXPORT_PISGA = 'export_pisga';

	const RULE_LAST_60_DAYS_OR_UP_UNTIL_SEASON_CHANGE = 1;
	const RULE_LAST_60_DAYS_WITHOUT_SEASONS = 2;
	const RULE_LAST_14_DAYS = 3;
	const RULE_SAME_PERIOD_LAST_YEAR = 4;


	const CATEGORY_ELECTRICITY = 1;
	const CATEGORY_AIR = 2;


	public function rules()
	{
		return [
			[['meter_id', 'channel_id'], 'filter', 'filter' => 'strip_tags'],
			[['meter_id', 'channel_id'], 'filter', 'filter' => 'trim'],
			[['meter_id', 'channel_id'], 'required'],
			[['meter_id', 'channel_id'], 'string', 'max' => 255],

			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
		];
	}

	public function attributeLabels()
	{
		return [
			'id' => Yii::t('common.meter', 'ID'),
			'meter_id' => Yii::t('common.meter', 'Meter ID'),
			'channel_id' => Yii::t('common.meter', 'Channel ID'),
			'date' => Yii::t('common.meter', 'Reading date'),

			'shefel' => Yii::t('common.meter', 'Shefel'),
			'geva' => Yii::t('common.meter', 'Geva'),
			'pisga' => Yii::t('common.meter', 'Pisga'),
			'reading_shefel' => Yii::t('common.meter', 'Reading shefel'),
			'reading_geva' => Yii::t('common.meter', 'Reading geva'),
			'reading_pisga' => Yii::t('common.meter', 'Reading pisga'),
			'max_shefel' => Yii::t('common.meter', 'Max shefel'),
			'max_geva' => Yii::t('common.meter', 'Max geva'),
			'max_pisga' => Yii::t('common.meter', 'Max pisga'),
			'export_shefel' => Yii::t('common.meter', 'Export shefel'),
			'export_geva' => Yii::t('common.meter', 'Export geva'),
			'export_pisga' => Yii::t('common.meter', 'Export pisga'),
			'kvar_shefel' => Yii::t('common.meter', 'KVAR shefel'),
			'kvar_geva' => Yii::t('common.meter', 'KVAR geva'),
			'kvar_pisga' => Yii::t('common.meter', 'KVAR pisga'),

			'status' => Yii::t('common.meter', 'Status'),
			'created_at' => Yii::t('common.meter', 'Created at'),
			'modified_at' => Yii::t('common.meter', 'Modified at'),
			'created_by' => Yii::t('common.meter', 'Created by'),
			'modified_by' => Yii::t('common.meter', 'Modified by'),
		];
	}

	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::className(),
				'createdAtAttribute' => 'created_at',
				'updatedAtAttribute' => 'modified_at',
			],
			[
				'class' => UserIdBehavior::className(),
				'createdByAttribute' => 'created_by',
				'modifiedByAttribute' => 'modified_by',
			],
			[
				'class' => ToTimestampBehavior::className(),
				'attributes' => [
					'date',
				],
			],
		];
	}



	public function getRelationMeter()
	{
		return $this->hasOne(Meter::className(), ['name' => 'meter_id']);
	}

	public function getRelationMeterChannel()
	{
		return $this->hasOne(MeterChannel::className(), ['channel' => 'channel_id']);
	}

	public static function getListStatuses()
	{
		return [
			self::STATUS_INACTIVE => Yii::t('common.meter', 'Inactive'),
			self::STATUS_ACTIVE => Yii::t('common.meter', 'Active'),
		];
	}

	public function getAliasStatus()
	{
		$list = self::getListStatuses();
		return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
	}

	public static function getAliasSeasonAvgConstant($season)
	{
		$list = self::getListSeasonAvgConstants();
		return (isset($list[$season])) ? $list[$season] : [];
	}

	public static function getListRulePeriods()
	{
		return [
			self::RULE_LAST_60_DAYS_OR_UP_UNTIL_SEASON_CHANGE => Yii::t('common.meter', 'Last 60 days or up until season change'),
			self::RULE_LAST_60_DAYS_WITHOUT_SEASONS => Yii::t('common.meter', 'Last 60 days without seasons'),
			self::RULE_LAST_14_DAYS => Yii::t('common.meter', 'Last 14 days'),
			self::RULE_SAME_PERIOD_LAST_YEAR => Yii::t('common.meter', 'Same period last year'),
		];
	}
}