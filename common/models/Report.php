<?php

namespace common\models;

use common\components\calculators\data\SiteData;
use common\models\pdfs\reports\PdfViewReportEnergy;
use common\models\pdfs\reports\PdfViewReportKwhPerSite;
use common\models\pdfs\reports\PdfViewReportMeters;
use common\models\pdfs\reports\PdfViewReportNisKwhPerSite;
use common\models\pdfs\reports\PdfViewReportNisPerSite;
use common\models\pdfs\reports\PdfViewReportRatesComprasion;
use common\models\pdfs\reports\PdfViewReportSummaryPerSite;
use common\models\pdfs\reports\PdfViewReportTenantBills;
use common\models\pdfs\reports\PdfViewReportYearly;
use Yii;
use yii\base\InvalidCallException;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use common\helpers\FileHelper;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;
use common\components\behaviors\ToTimestampBehavior;
use common\components\i18n\LanguageSelector;

/**
 * Report is the class for the table "report".
 * @property $data_usage_method
 * @property $is_automatically_generated
 * @property $parent_id
 * @property $is_public
 * @property $type
 * @property $level
 * @property $to_date
 * @property $from_date
 * @property $site_owner_id
 * @property $site_id
 */
class Report extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    const LEVEL_SITE = 1;
    const LEVEL_TENANT = 2;

    const TYPE_NIS = 1;
    const TYPE_KWH = 2;
    const TYPE_SUMMARY = 3;
    const TYPE_METERS = 4;
    const TYPE_NIS_KWH = 5;
    const TYPE_RATES_COMPRASION = 6;
    const TYPE_TENANT_BILLS = 7;
    const TYPE_YEARLY = 8;
    const TYPE_ENERGY = 9;

    const TENANT_BILL_REPORT_BY_MAIN_METERS = 1;
    const TENANT_BILL_REPORT_BY_FIRST_RULE = 2;
    const TENANT_BILL_REPORT_BY_MANUAL_COP = 3;

    public static function tableName() {
        return 'report';
    }


    public function rules() {
        return [
            [['site_owner_id', 'site_id', 'from_date', 'to_date', 'level', 'type'], 'required'],
            [['site_owner_id', 'site_id', 'parent_id'], 'integer'],
            ['level', 'in', 'range' => array_keys(self::getListLevels()), 'skipOnEmpty' => false],
            ['type', 'in', 'range' => array_keys(self::getListTypes()), 'skipOnEmpty' => false],
            [['is_public', 'is_automatically_generated'], 'default', 'value' => self::NO],
            [['is_public', 'is_automatically_generated'], 'boolean'],
            ['data_usage_method', 'in', 'range' => [
                Meter::DATA_USAGE_METHOD_IMPORT,
                Meter::DATA_USAGE_METHOD_IMPORT_PLUS_EXPORT,
                Meter::DATA_USAGE_METHOD_IMPORT_MINUS_EXPORT,
                Meter::DATA_USAGE_METHOD_EXPORT,
                Meter::DATA_USAGE_METHOD_DEFAULT,
            ], 'skipOnEmpty' => true],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
        ];
    }


    public function attributeLabels() {
        return [
            'id' => Yii::t('common.report', 'ID'),
            'site_owner_id' => Yii::t('common.report', 'Client'),
            'site_id' => Yii::t('common.report', 'Site'),
            'from_date' => Yii::t('common.report', 'From date'),
            'to_date' => Yii::t('common.report', 'To date'),
            'level' => Yii::t('common.report', 'Level'),
            'type' => Yii::t('common.report', 'Type'),
            'is_public' => Yii::t('common.report', 'Published to client'),
            'is_automatically_generated' => Yii::t('common.report', 'Is automatically generated'),
            'status' => Yii::t('common.report', 'Status'),
            'created_at' => Yii::t('common.report', 'Created at'),
            'modified_at' => Yii::t('common.report', 'Modified at'),
            'created_by' => Yii::t('common.report', 'Issued by'),
            'issued_by' => Yii::t('common.report', 'Issued by'),
            'modified_by' => Yii::t('common.report', 'Modified by'),
            'site_owner_name' => Yii::t('common.report', 'Client name'),
            'site_name' => Yii::t('common.report', 'Site name'),
        ];
    }


    public function behaviors() {
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
                    'from_date',
                    'to_date',
                ],
            ],
        ];
    }


    public function getRelationSiteOwner() {
        return $this->hasOne(User::className(), ['id' => 'site_owner_id']);
    }


    public function getRelationSite() {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }


    /**
     * @return ActiveQuery
     */
    public function getRelationReportFiles() {
        return $this->hasMany(ReportFile::className(), ['report_id' => 'id']);
    }


    public function getRelationTenantReports() {
        return $this->hasMany(TenantReport::className(), ['report_id' => 'id']);
    }


    public function getRelationUserCreator() {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }


    public static function getListStatuses() {
        return [
            self::STATUS_INACTIVE => Yii::t('common.report', 'Inactive'),
            self::STATUS_ACTIVE => Yii::t('common.report', 'Active'),
        ];
    }


    public function getAliasStatus() {
        $list = self::getListStatuses();
        return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
    }


    public static function getListLevels() {
        return [
            self::LEVEL_SITE => Yii::t('common.report', 'Site'),
            self::LEVEL_TENANT => Yii::t('common.report', 'Tenant'),
        ];
    }


    public function getAliasLevel() {
        $list = self::getListLevels();
        return (isset($list[$this->level])) ? $list[$this->level] : $this->level;
    }


    public static function getListTypes() {
        return [
            self::TYPE_NIS => Yii::t('common.report', 'NIS report'),
            self::TYPE_KWH => Yii::t('common.report', 'Kwh report'),
            self::TYPE_NIS_KWH => Yii::t('common.report', 'NIS + Kwh report'),
            //self::TYPE_SUMMARY => Yii::t('common.report', 'Summary report'),
            //self::TYPE_METERS => Yii::t('common.report', 'Meters report'),
            //self::TYPE_RATES_COMPRASION => Yii::t('common.report', 'Rates comparison report'),
            self::TYPE_TENANT_BILLS => Yii::t('common.report', 'Tenant bills report'),
            //self::TYPE_YEARLY => Yii::t('common.report', 'Yearly report'),
            //self::TYPE_ENERGY => Yii::t('common.report', 'Energy report'),
        ];
    }


    public static function getTenantListTypes() {
        return [
			self::TYPE_NIS => Yii::t('common.report', 'NIS report'),
			self::TYPE_KWH => Yii::t('common.report', 'Kwh report'),
			self::TYPE_NIS_KWH => Yii::t('common.report', 'NIS + Kwh report'),
//			self::TYPE_SUMMARY => Yii::t('common.report', 'Summary report'),
//			self::TYPE_RATES_COMPRASION => Yii::t('common.report', 'Rates comparison report'),
            self::TYPE_TENANT_BILLS => Yii::t('common.report', 'Tenant bills report'),
//			self::TYPE_YEARLY => Yii::t('common.report', 'Yearly report'),
        ];
    }


    public static function getAutoIssueListTypes() {
        return [
            self::TYPE_TENANT_BILLS => Yii::t('common.report', 'Tenant bills report'),
            self::TYPE_NIS => Yii::t('common.report', 'NIS report'),
            self::TYPE_KWH => Yii::t('common.report', 'Kwh report'),
            self::TYPE_NIS_KWH => Yii::t('common.report', 'NIS + Kwh report'),
        ];
    }

    public static function getTenantBillReportTypes()
    {
        return [
            self::TENANT_BILL_REPORT_BY_MANUAL_COP => Yii::t('common.report','Manual COP'),
            self::TENANT_BILL_REPORT_BY_MAIN_METERS => Yii::t('common.report', 'Main meters (electric+air)'),
            self::TENANT_BILL_REPORT_BY_FIRST_RULE => Yii::t('common.report', 'No main air meter (calculated from sum of tenants)'),
        ];
    }

    public function getAliasType() {
        return ArrayHelper::getValue(self::getListTypes(), $this->type, $this->type);
    }


    public function getAliasPublished() {
        return ArrayHelper::getValue(self::getListYesNo(), $this->is_public);
    }


    public function getFilePath($type) {
        if(($model = $this->getRelationReportFiles()->andWhere([
                                                                   'file_type' => $type,
                                                                   'language' => Yii::$app->language,
                                                               ])->one()) != null
        ) {
            return $model->getFilePath();
        }
        if(($model = $this->getRelationReportFiles()->andWhere([
                                                                   'file_type' => $type,
                                                               ])->one()) != null
        ) {
            return $model->getFilePath();
        }
    }


    public static function getReportLanguage() {
        if(($value = Yii::$app->cache->get('report_language')) != null) {
            return $value;
        }
        else {
            return LanguageSelector::LANGUAGE_HE;
        }
    }


    public static function setReportLanguage($language) {
        Yii::$app->cache->delete('report_language');
        return Yii::$app->cache->set('report_language', $language, 0);
    }


    /**
     * Generate PDF
     * @param SiteData $data
     * @param array $additional_parameters
     * @return ReportFile
     * @throws BadRequestHttpException
     */
    public function generatePdf($data, array $additional_parameters = []) {
        $prefix = Yii::$app->language;
        $language = Report::getReportLanguage();
        Yii::$app->language = $language;
        $filename = FileHelper::createDirectory(ReportFile::FILE_PATH) . "/report-$language." . uniqid() . ".pdf";
        switch($this->type) {
            case Report::TYPE_TENANT_BILLS:
                $view = new PdfViewReportTenantBills();
                break;
            case Report::TYPE_NIS_KWH:
                $view = new PdfViewReportNisKwhPerSite();
                break;
            case Report::TYPE_NIS:
                $view = new PdfViewReportNisPerSite();
                break;
            case Report::TYPE_KWH:
                $view = new PdfViewReportKwhPerSite();
                break;
            default:
                break;
        }
        if(!isset($view)) {
          throw new InvalidCallException('The view file is undefined');
        }

        $view->setModel($this);
        //VarDumper::dump($data, 100, true);
        $view->setParams(ArrayHelper::merge(['data' => $data], [
            'report' => $this,
            'additional_parameters' => $additional_parameters,
        ]));
        $view->file($filename);
        $model_file = new ReportFile();
        $model_file->report_id = $this->id;
        $model_file->language = $language;
        $model_file->file_type = ReportFile::FILE_TYPE_PDF;
        $model_file->file = $filename;
        if(!$model_file->save()) {
            throw new BadRequestHttpException(implode(' ', $model_file->getFirstErrors()));
        }
        Yii::$app->language = $prefix;
        return $model_file;
    }


    /**
     * Generate Excel
     * @param array $data
     * @param array $additional_parameters
     *
     * @return \common\models\ReportFile
     * @throws \yii\web\BadRequestHttpException
     */
    public function generateExcel($data, array $additional_parameters = []) {

        $prefix = Yii::$app->language;
        $language = Report::getReportLanguage();
        Yii::$app->language = $language;
        $filename = FileHelper::createDirectory(ReportFile::FILE_PATH) . "/report-$language." . uniqid() . ".xls";
        switch($this->type) {
            case Report::TYPE_NIS:
                $view = new \common\models\excels\reports\ExcelViewReportNisPerSite();
                break;
            case Report::TYPE_KWH:
                $view = new \common\models\excels\reports\ExcelViewReportKwhPerSite();
                break;
            case Report::TYPE_NIS_KWH:
                $view = new \common\models\excels\reports\ExcelViewReportNisKwhPerSite();
                break;
            case Report::TYPE_TENANT_BILLS:
                $view = new \common\models\excels\reports\ExcelViewReportTenantBills();
                break;
            default:
                break;
        }
      if(!isset($view)) {
        throw new InvalidCallException('The view file is undefined');
      }
        $view->setModel($this);
        $test = ArrayHelper::merge(['data' => $data], [
            'report' => $this,
            'additional_parameters' => $additional_parameters,
        ]);
        $view->setParams(ArrayHelper::merge(['data' => $data], [
            'report' => $this,
            'additional_parameters' => $additional_parameters,
        ]));
        $view->file($filename);
        $model_file = new ReportFile();
        $model_file->report_id = $this->id;
        $model_file->language = $language;
        $model_file->file_type = ReportFile::FILE_TYPE_EXCEL;
        $model_file->file = $filename;
        if(!$model_file->save()) {
            throw new BadRequestHttpException(implode(' ', $model_file->getFirstErrors()));
        }
        Yii::$app->language = $prefix;
        return $model_file;
    }


    /**
     * Generate DAT
     * @param array $data
     * @param array $additional_parameters
     */
    public function generateDat(array $data, array $additional_parameters = []) {
        $prefix = Yii::$app->language;
        $language = Report::getReportLanguage();
        Yii::$app->language = $language;
        switch($this->type) {
            case Report::TYPE_TENANT_BILLS:
                $view = new \common\models\dat\reports\DatViewReportTenantBills();
                break;
            default:
                break;
        }
        $view->setModel($this);
        $view->setParams(ArrayHelper::merge($data, [
            'report' => $this,
            'additional_parameters' => $additional_parameters,
        ]));
        /**
         * Create DAT
         */
        $datFilename = FileHelper::createDirectory(ReportFile::FILE_PATH) . "/report-$language." . uniqid() . ".dat";
        $view->file($datFilename);
        $model_file_dat = new ReportFile();
        $model_file_dat->report_id = $this->id;
        $model_file_dat->language = $language;
        $model_file_dat->file_type = ReportFile::FILE_TYPE_DAT;
        $model_file_dat->file = $datFilename;
        if(!$model_file_dat->save()) {
            throw new BadRequestHttpException(implode(' ', $model_file_dat->getFirstErrors()));
        }
        /**
         * Create TXT
         */
        $txtFilename = FileHelper::createDirectory(ReportFile::FILE_PATH) . "/report-$language." . uniqid() . ".txt";
        $view->file($txtFilename);
        $model_file_dat = new ReportFile();
        $model_file_dat->report_id = $this->id;
        $model_file_dat->language = $language;
        $model_file_dat->file_type = ReportFile::FILE_TYPE_TXT;
        $model_file_dat->file = $txtFilename;
        if(!$model_file_dat->save()) {
            throw new BadRequestHttpException(implode(' ', $model_file_dat->getFirstErrors()));
        }
        Yii::$app->language = $prefix;
        return $model_file_dat;
    }
}
