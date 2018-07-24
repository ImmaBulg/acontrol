<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

use common\components\i18n\LanguageSelector;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * ReportFile is the class for the table "report_file".
 */
class ReportFile extends ActiveRecord
{
	const FILE_PATH = '@static/report/:Y/:m/:d';

	const FILE_TYPE_PDF = 'pdf';
	const FILE_TYPE_EXCEL = 'xls';
	const FILE_TYPE_DAT = 'dat';
	const FILE_TYPE_TXT = 'txt';

	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;

	public static function tableName()
	{
		return 'report_file';
	}

	public function rules()
	{
		return [
			[['report_id', 'file', 'file_type', 'language'], 'required'],
			[['report_id'], 'integer'],
			[['file'], 'string'],
			['file_type', 'in', 'range' => array_keys(self::getListFileTypes()), 'skipOnEmpty' => false],
			['language', 'in', 'range' => array_keys(LanguageSelector::getSupportedLanguages()), 'skipOnEmpty' => false],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
		];
	}

	public function attributeLabels()
	{
		return [
			'id' => Yii::t('common.report', 'ID'),
			'report_id' => Yii::t('common.report', 'Report'),
			'file' => Yii::t('common.report', 'File'),
			'file_type' => Yii::t('common.report', 'File type'),
			'language' => Yii::t('common.report', 'Language'),
			'status' => Yii::t('common.report', 'Status'),
			'created_at' => Yii::t('common.report', 'Created at'),
			'modified_at' => Yii::t('common.report', 'Modified at'),
			'created_by' => Yii::t('common.report', 'Created by'),
			'modified_by' => Yii::t('common.report', 'Modified by'),
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
		];
	}

	public function getRelationReport()
	{
		return $this->hasOne(Report::className(), ['id' => 'report_id']);
	}

	public static function getListStatuses()
	{
		return [
			self::STATUS_INACTIVE => Yii::t('common.report', 'Inactive'),
			self::STATUS_ACTIVE => Yii::t('common.report', 'Active'),
		];
	}

	public function getAliasStatus()
	{
		$list = self::getListStatuses();
		return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
	}

	public static function getListFileTypes()
	{
		return [
			self::FILE_TYPE_PDF => Yii::t('common.report', 'PDF'),
			self::FILE_TYPE_EXCEL => Yii::t('common.report', 'Excel'),
			self::FILE_TYPE_DAT => Yii::t('common.report', 'DAT'),
			self::FILE_TYPE_TXT => Yii::t('common.report', 'TXT'),
		];
	}

	public function getAliasFileType()
	{
		$list = self::getListFileTypes();
		return (isset($list[$this->file_type])) ? $list[$this->file_type] : $this->file_type;
	}

	public function getFilePath()
	{
		return Yii::$app->urlManagerStatic->createAbsoluteUrl([$this->file]);
	}
}
