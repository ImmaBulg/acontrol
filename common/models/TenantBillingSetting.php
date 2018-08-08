<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * TenantBillingSetting is the class for the table "tenant_billing_setting".
 * @property  $fixed_payment
 * @property  RateType $relationRateType
 * @property  integer rate_type_id
 * @property  integer $id
 * @property  integer $tenant_id
 * @property  integer $billing_content
 * @property  integer $id_with_client
 * @property  integer $accounting_number
 * @property  integer $site_id
 * @property  string $comment
 * @property string $irregular_hours_from
 * @property string $irregular_hours_to
 * @property float $irregular_additional_percent
 */
class TenantBillingSetting extends ActiveRecord
{
    public static function tableName() {
        return 'tenant_billing_setting';
    }


    public function rules() {
        return [
            [['comment', 'billing_content', 'id_with_client', 'accounting_number'], 'filter', 'filter' => 'strip_tags'],
            [['comment', 'billing_content', 'id_with_client', 'accounting_number'], 'filter', 'filter' => 'trim'],
            [['tenant_id', 'site_id'], 'required'],
            [['tenant_id', 'site_id', 'rate_type_id'], 'integer'],
            [['fixed_payment'], 'number', 'min' => 0],
            [['fixed_payment'], 'compare', 'compareValue' => 0, 'operator' => '>='],
            [['comment', 'billing_content'], 'string'],
            [['id_with_client', 'accounting_number'], 'string', 'max' => 255],
            ['irregular_additional_percent', 'number'],
            [['irregular_hours_from', 'irregular_hours_to'], 'string'],
        ];
    }


    public function attributeLabels() {
        return [
            'tenant_id' => Yii::t('common.tenant', 'Tenant ID'),
            'site_id' => Yii::t('common.tenant', 'Site ID'),
            'rate_type_id' => Yii::t('common.tenant', 'Rate type'),
            'comment' => Yii::t('common.tenant', 'Comment'),
            'fixed_payment' => Yii::t('common.tenant', 'Fixed payment'),
            'id_with_client' => Yii::t('common.tenant', 'ID with client'),
            'accounting_number' => Yii::t('common.tenant', 'Accounting number'),
            'billing_content' => Yii::t('common.tenant', 'Billing content'),
            'created_at' => Yii::t('common.tenant', 'Created at'),
            'modified_at' => Yii::t('common.tenant', 'Modified at'),
            'created_by' => Yii::t('common.tenant', 'Created by'),
            'modified_by' => Yii::t('common.tenant', 'Modified by'),
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
        ];
    }


    public function getRelationSite() {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }


    public function getRelationTenant() {
        return $this->hasOne(Tenant::className(), ['id' => 'tenant_id']);
    }


    public function getRelationRateType() {
        return $this->hasOne(RateName::className(), ['id' => 'rate_type_id']);
    }


    public function getAliasRateType() {
        if(($rate_type = $this->relationRateType) != null) {
            return $rate_type->name;
        }
    }


    public function getIrregularHoursFrom() {
        return $this->irregular_hours_from;
    }


    public function getIrregularHoursTo() {
        return $this->irregular_hours_to;
    }

}
