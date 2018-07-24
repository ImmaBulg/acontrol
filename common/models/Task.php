<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\web\BadRequestHttpException;

use common\components\rbac\Role;
use common\components\i18n\Formatter;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;
use common\components\behaviors\ToTimestampBehavior;

/**
 * Task is the class for the table "task".
 */
class Task extends ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_PENDING_APPROVAL = 2;
	const STATUS_DELETED = 3;

	const URGENCY_LOW = 0;
	const URGENCY_NORMAL = 1;
	const URGENCY_HIGH = 2;

	const TYPE_HELPDESK = 1;
	const TYPE_ALERT = 2;
	const TYPE_ISSUE_ALERT = 3;

	const COLOR_RED = 'red';
	const COLOR_DARK_RED = 'darkred';
	const COLOR_ORANGE = 'orange';
	const COLOR_BLUE = 'blue';

	public static function tableName()
	{
		return 'task';
	}

	public function rules()
	{
		return [
			[['description'], 'filter', 'filter' => 'strip_tags'],
			[['description'], 'filter', 'filter' => 'trim'],
			[['user_id', 'site_id', 'date', 'description'], 'required'],
			[['user_id', 'site_id', 'site_contact_id', 'meter_id', 'channel_id'], 'integer'],
			[['ip_address'], 'string', 'max' => 255],
			[['description'], 'string'],
			[['is_sent'], 'boolean'],
			['type', 'default', 'value' => self::TYPE_HELPDESK],
			['type', 'in', 'range' => array_keys(self::getListTypes()), 'skipOnEmpty' => true],
			['urgency', 'default', 'value' => self::URGENCY_LOW],
			['urgency', 'in', 'range' => array_keys(self::getListUrgencies()), 'skipOnEmpty' => true],
			['color', 'in', 'range' => array_keys(self::getListColors()), 'skipOnEmpty' => true],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
		];
	}

	public function attributeLabels()
	{
		return [
			'user_id' => Yii::t('common.task', 'User ID'),
			'site_id' => Yii::t('common.task', 'Site ID'),
			'site_contact_id' => Yii::t('common.task', 'Site contact ID'),
			'meter_id' => Yii::t('common.task', 'Meter ID'),
			'channel_id' => Yii::t('common.task', 'Channel ID'),
			'description' => Yii::t('common.task', 'Description'),
			'urgency' => Yii::t('common.task', 'Urgency'),
			'status' => Yii::t('common.task', 'Status'),
			'date' => Yii::t('common.task', 'Date'),
			'color' => Yii::t('common.task', 'Color'),
			'ip_address' => Yii::t('common.task', 'Ip address'),
			'is_sent' => Yii::t('common.task', 'Sent'),
			'created_at' => Yii::t('common.task', 'Created at'),
			'modified_at' => Yii::t('common.task', 'Modified at'),
			'created_by' => Yii::t('common.task', 'Created by'),
			'modified_by' => Yii::t('common.task', 'Modified by'),

			'user_name' => Yii::t('common.task', 'Assignee'),
			'user_role' => Yii::t('common.task', 'Role'),
			'site_name' => Yii::t('common.task', 'Site name'),
			'site_contact_name' => Yii::t('common.task', 'Contact name'),
			'meter_name' => Yii::t('common.task', 'Meter ID'),
			'channel_name' => Yii::t('common.task', 'Channel'),
			'date_timestamp' => Yii::t('common.task', 'Timestamp'),
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

	public function getRelationUser()
	{
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}

	public function getRelationSite()
	{
		return $this->hasOne(Site::className(), ['id' => 'site_id']);
	}

	public function getRelationSiteContact()
	{
		return $this->hasOne(SiteContact::className(), ['id' => 'site_contact_id']);
	}

	public function getRelationMeter()
	{
		return $this->hasOne(Meter::className(), ['id' => 'meter_id']);
	}

	public function getRelationMeterChannel()
	{
		return $this->hasOne(MeterChannel::className(), ['id' => 'channel_id']);
	}

	public static function getListTypes()
	{
		return [
			self::TYPE_HELPDESK => Yii::t('common.task', 'Helpdesk'),
			self::TYPE_ISSUE_ALERT => Yii::t('common.task', 'Issue Alert'),
			self::TYPE_ALERT => Yii::t('common.task', 'Alert'),
		];
	}

	public function getAliasType()
	{
		$list = self::getListTypes();
		return (isset($list[$this->type])) ? $list[$this->type] : $this->type;
	}

	public static function getListStatuses()
	{
		return [
			self::STATUS_INACTIVE => Yii::t('common.task', 'Inactive'),
			self::STATUS_PENDING_APPROVAL => Yii::t('common.task', 'Pending approval'),
			self::STATUS_ACTIVE => Yii::t('common.task', 'Active'),
		];
	}

	public function getAliasStatus()
	{
		$list = self::getListStatuses();
		return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
	}

	public static function getListUrgencies()
	{
		return [
			self::URGENCY_LOW => Yii::t('common.task', 'Low'),
			self::URGENCY_NORMAL => Yii::t('common.task', 'Normal'),
			self::URGENCY_HIGH => Yii::t('common.task', 'High'),
		];
	}

	public function getAliasUrgency()
	{
		$list = self::getListUrgencies();
		return (isset($list[$this->urgency])) ? $list[$this->urgency] : $this->urgency;
	}

	public static function getListColors()
	{
		return [
			self::COLOR_RED => Yii::t('common.task', 'Red'),
			self::COLOR_DARK_RED => Yii::t('common.task', 'Dark red'),
			self::COLOR_ORANGE => Yii::t('common.task', 'Orange'),
			self::COLOR_BLUE => Yii::t('common.task', 'Blue'),
		];
	}

	public function getAliasColor()
	{
		$list = self::getListColors();
		return (isset($list[$this->color])) ? $list[$this->color] : $this->color;
	}

	public static function getListAssignees()
	{
		$query = (new Query())->select(['t.id', 't.name'])
		->from(User::tableName(). ' t')
		->innerJoin(static::tableName(). ' task', 'task.user_id = t.id')
		->groupBy(['t.id']);
		$rows = $query->all();
		return ArrayHelper::map($rows, 'id', 'name');
	}

	public static function getListUsers()
	{
		$query = (new Query())->from(User::tableName(). ' t')
		->andWhere(['in', 't.role', [
			Role::ROLE_TECHNICIAN,
			Role::ROLE_ADMIN,
		]])
		->andWhere(['in', 't.status', [
			User::STATUS_ACTIVE,
		]]);
		$rows = $query->all();
		return ArrayHelper::map($rows, 'id', 'name');
	}

	public static function getListRoles()
	{
		return [
			Role::ROLE_TECHNICIAN => Yii::t('common.common', 'Technician'),
			Role::ROLE_ADMIN => Yii::t('common.common', 'Administrator'),
		];
	}

	public static function getAssigneeId()
	{
		if (($value = Yii::$app->cache->get('task_assignee_id')) != null) {
			return $value;
		} else {
			return (new Query)->select(['id'])->from(User::tableName())
			->andWhere([
				'role' => Role::ROLE_ADMIN,
				'status' => User::STATUS_ACTIVE,
			])->scalar();
		}
	}

	public static function setAssigneeId($assignee)
	{
		return Yii::$app->cache->set('task_assignee_id', $assignee, 0);
	}

	public static function getAssigneeName()
	{
		if (($assignee = User::findOne(self::getAssigneeId())) != null) {
			return $assignee->name;
		}
	}

	public static function getAssigneeRole()
	{
		if (($assignee = User::findOne(self::getAssigneeId())) != null) {
			return $assignee->role;
		}
	}

	/**
	 * Create new alert
	 * 
	 * @param [type] $site_id     [description]
	 * @param [type] $description [description]
	 * @param [type] $date        [description]
	 * @param [type] $urgency     [description]
	 * @param [type] $color       [description]
	 * @param [type] $meter_id    [description]
	 * @param [type] $channel_id  [description]
	 */
	public static function addAlert($site_id, $description, $date, $urgency = self::URGENCY_NORMAL, $color = self::COLOR_RED, $meter_id = null, $channel_id = null)
	{
		$sql_date_format = Formatter::SQL_DATE_FORMAT;
		$model = Task::find()->andWhere([
			'site_id' => $site_id,
			'description' => $description,
			'type' => Task::TYPE_ISSUE_ALERT,
			'urgency' => $urgency,
			'color' => $color,
		])->andWhere("DATE_FORMAT(FROM_UNIXTIME(date), '$sql_date_format') = :date", [
			'date' => Yii::$app->formatter->asDate($date),
		])->andFilterWhere([
			'meter_id' => $meter_id,
			'channel_id' => $channel_id,
		])->one();

		if ($model == null) {
			$model = new Task();
			$model->user_id = Task::getAssigneeId();
			$model->site_id = $site_id;
			$model->meter_id = $meter_id;
			$model->channel_id = $channel_id;
			$model->description = $description;
			$model->date = $date;
			$model->type = Task::TYPE_ISSUE_ALERT;
			$model->urgency = $urgency;
			$model->color = $color;

			if (!(Yii::$app->request instanceof \yii\console\Request)) {
				$model->ip_address = Yii::$app->request->userIp;
			}

			if (!$model->save()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
			}
		}
	}
}
