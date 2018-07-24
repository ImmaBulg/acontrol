<?php
namespace api\models\forms;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\ElectricityMeterRawData;
use common\components\i18n\Formatter;

/**
 * FormMeterRawDataSingle is the class for meter raw data single create/edit.
 */
class FormMeterRawDataSingle extends \yii\base\Model
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_EDIT = 'edit';
    const _1_HOUR = 3600;

    public $meter_id;
    public $channel_id;
    public $date;
    public $reading_shefel;
    public $reading_geva;
    public $reading_pisga;
    public $consumption_shefel;
    public $consumption_geva;
    public $consumption_pisga;
    public $consumption_export_shefel;
    public $consumption_export_geva;
    public $consumption_export_pisga;
    public $max_shefel;
    public $max_geva;
    public $max_pisga;
    public $export_shefel;
    public $export_geva;
    public $export_pisga;
    public $kvar_shefel;
    public $kvar_geva;
    public $kvar_pisga;
    public $is_main;


    public function rules() {
        return [
            [['meter_id', 'channel_id', 'date'], 'required'],
            ['meter_id', 'match', 'pattern' => Meter::NAME_VALIDATION_PATTERN],
            [['channel_id'], 'integer'],
            ['date', 'date', 'format' => Formatter::PHP_DATE_TIME_FORMAT],
            [['max_shefel', 'max_geva', 'max_pisga'], 'number'],
            [['export_shefel', 'export_geva', 'export_pisga'], 'number'],
            [['kvar_shefel', 'kvar_geva', 'kvar_pisga'], 'number'],
            [['reading_shefel', 'reading_geva', 'reading_pisga'], 'number'],
            [['consumption_shefel', 'consumption_geva', 'consumption_pisga', 'consumption_export_shefel',
              'consumption_export_geva', 'consumption_export_pisga','is_main'], 'number'],
            ['consumption_shefel', 'required', 'when' => function ($model) {
                return is_null($model->reading_shefel);
            }],
            ['consumption_geva', 'required', 'when' => function ($model) {
                return is_null($model->reading_geva);
            }],
            ['consumption_pisga', 'required', 'when' => function ($model) {
                return is_null($model->reading_pisga);
            }],
            ['consumption_export_shefel', 'required', 'when' => function ($model) {
                return is_null($model->export_shefel);
            }],
            ['consumption_export_geva', 'required', 'when' => function ($model) {
                return is_null($model->export_geva);
            }],
            ['consumption_export_pisga', 'required', 'when' => function ($model) {
                return is_null($model->export_pisga);
            }],
            [['consumption_shefel', 'consumption_geva', 'consumption_pisga'], 'validateConsumptionImport'],
            [['consumption_export_shefel', 'consumption_export_geva', 'consumption_export_pisga'],
             'validateConsumptionExport'],
        ];
    }


    public function validateConsumptionImport($attribute, $params) {
        if(!$this->hasErrors()) {
            switch($attribute) {
                case 'consumption_shefel':
                    $user_attribute = 'shefel';
                    $system_attribute = 'reading_shefel';
                    break;
                case 'consumption_geva':
                    $user_attribute = 'geva';
                    $system_attribute = 'reading_geva';
                    break;
                case 'consumption_pisga':
                    $user_attribute = 'pisga';
                    $system_attribute = 'reading_pisga';
                    break;
                default:
                    break;
            }
            $data = (new Query())
                ->select(["IFNULL(`$user_attribute`, `$system_attribute`) as $user_attribute"])
                ->from(ElectricityMeterRawData::tableName() . ' t')->andWhere([
                                                                                  't.meter_id' => $this->meter_id,
                                                                                  't.channel_id' => $this->channel_id,
                                                                              ])
                ->andWhere([
                               'date' => Yii::$app->formatter->asTimestamp($this->date) - self::_1_HOUR
                           ])
                ->one();
            if(!$data || is_null($data[$user_attribute])) {
                $data[$user_attribute] = 0;
            }
            $this->$system_attribute = $data[$user_attribute] + $this->$attribute;
        }
    }


    public function validateConsumptionExport($attribute, $params) {
        if(!$this->hasErrors()) {
            switch($attribute) {
                case 'consumption_export_shefel':
                    $system_attribute = 'export_shefel';
                    break;
                case 'consumption_export_geva':
                    $system_attribute = 'export_geva';
                    break;
                case 'consumption_export_pisga':
                    $system_attribute = 'export_pisga';
                    break;
                default:
                    break;
            }
            $data = (new Query())
                ->select("$system_attribute")
                ->from(ElectricityMeterRawData::tableName() . ' t')->andWhere([
                                                                                  't.meter_id' => $this->meter_id,
                                                                                  't.channel_id' => $this->channel_id,
                                                                              ])
                ->andWhere([
                               'date' => Yii::$app->formatter->asTimestamp($this->date) - self::_1_HOUR
                           ])->one();
            if(!$data || is_null($data[$system_attribute])) {
                $data[$system_attribute] = 0;
            }
            $this->$system_attribute = $data[$system_attribute] + $this->$attribute;
        }
    }


    public function attributeLabels() {
        return [
            'meter_id' => Yii::t('api.meter', 'Meter ID'),
            'channel_id' => Yii::t('api.meter', 'Channel ID'),
            'date' => Yii::t('api.meter', 'Reading date'),
            'reading_shefel' => Yii::t('api.meter', 'Reading shefel'),
            'reading_geva' => Yii::t('api.meter', 'Reading geva'),
            'reading_pisga' => Yii::t('api.meter', 'Reading pisga'),
            'consumption_shefel' => Yii::t('api.meter', 'Consumption shefel'),
            'consumption_geva' => Yii::t('api.meter', 'Consumption geva'),
            'consumption_pisga' => Yii::t('api.meter', 'Consumption pisga'),
            'consumption_export_shefel' => Yii::t('api.meter', 'Consumption export shefel'),
            'consumption_export_geva' => Yii::t('api.meter', 'Consumption export geva'),
            'consumption_export_pisga' => Yii::t('api.meter', 'Consumption export pisga'),
            'max_shefel' => Yii::t('api.meter', 'Max shefel'),
            'max_geva' => Yii::t('api.meter', 'Max geva'),
            'max_pisga' => Yii::t('api.meter', 'Max pisga'),
            'export_shefel' => Yii::t('api.meter', 'Export shefel'),
            'export_geva' => Yii::t('api.meter', 'Export geva'),
            'export_pisga' => Yii::t('api.meter', 'Export pisga'),
            'kvar_shefel' => Yii::t('api.meter', 'KVAR shefel'),
            'kvar_geva' => Yii::t('api.meter', 'KVAR geva'),
            'kvar_pisga' => Yii::t('api.meter', 'KVAR pisga'),
        ];
    }
}
