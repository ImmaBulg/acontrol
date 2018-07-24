<?php

namespace common\models;

use Yii;
use yii\helpers\Json;
use yii\behaviors\TimestampBehavior;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;
use yii\web\Request;

/**
 * Log is the class for the table "log".
 * @property $type
 * @property $action
 * @property $tokens
 * @property $ip_address
 * @property $status
 * @property $created_at
 * @property $modified_at
 * @property $created_by
 * @property $modified_by
 * @property $user_name
 */
class Log extends ActiveRecord
{
    const TYPE_CREATE = 1;
    const TYPE_UPDATE = 2;
    const TYPE_DELETE = 3;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;


    public static function tableName() {
        return 'log';
    }


    public function rules() {
        return [
            [['type', 'action'], 'required'],
            ['ip_address', 'required','when' => function () {
                return Yii::$app->request instanceof Request;
            }],
            [['action', 'tokens'], 'string'],
            [['ip_address'], 'string', 'max' => 255],
            ['type', 'in', 'range' => array_keys(self::getListTypes()), 'skipOnEmpty' => false],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
        ];
    }


    public function attributeLabels() {
        return [
            'id' => Yii::t('common.log', 'ID'),
            'type' => Yii::t('common.log', 'Type'),
            'action' => Yii::t('common.log', 'Action'),
            'tokens' => Yii::t('common.log', 'Tokens'),
            'ip_address' => Yii::t('common.log', 'Ip address'),
            'status' => Yii::t('common.log', 'Status'),
            'created_at' => Yii::t('common.log', 'Created at'),
            'modified_at' => Yii::t('common.log', 'Modified at'),
            'created_by' => Yii::t('common.log', 'Created by'),
            'modified_by' => Yii::t('common.log', 'Modified by'),
            'user_name' => Yii::t('common.log', 'User'),
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


    public function getRelationUserCreator() {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }


    public static function getListTypes() {
        return [
            self::TYPE_CREATE => Yii::t('common.log', 'Create'),
            self::TYPE_UPDATE => Yii::t('common.log', 'Update'),
            self::TYPE_DELETE => Yii::t('common.log', 'Delete'),
        ];
    }


    public function getAliasType() {
        $list = self::getListTypes();
        return (isset($list[$this->type])) ? $list[$this->type] : $this->type;
    }


    public static function getListStatuses() {
        return [
            self::STATUS_INACTIVE => Yii::t('common.log', 'Inactive'),
            self::STATUS_ACTIVE => Yii::t('common.log', 'Active'),
        ];
    }


    public function getAliasStatus() {
        $list = self::getListStatuses();
        return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
    }


    public function setFormattedTokens($tokens) {
        $this->tokens = Json::encode($tokens);
    }


    public function getFormattedTokens() {
        if($this->tokens != null) {
            return Json::decode($this->tokens);
        }
    }


    public function getAliasAction() {
        if($this->action != null) {
            return Yii::t('common.log', $this->action, $this->getFormattedTokens());
        }
    }


    public function setIp() {
        if(Yii::$app->request instanceof Request) {
            $this->ip_address = Yii::$app->request->getUserIP();
        }
    }


    public static function add($type, $action, $params) {
        $model_log = new Log();
        $model_log->type = $type;
        $model_log->action = $action;
        $model_log->setFormattedTokens($params);
        $model_log->setIp();
        $model_log->save();
    }
}
