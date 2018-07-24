<?php

namespace api\models\forms;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use api\models\Task;
use common\models\Site;
use common\models\User;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\SiteContact;
use common\components\i18n\Formatter;

/**
 * FormAlertDataSingle is the class for alert data single create/edit.
 */
class FormAlertDataSingle extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	public $site_id;
	public $site_contact_id;
	public $meter_id;
	public $channel_id;
	public $description;
	public $urgency;
	public $date;
	public $color;

	public function rules()
	{
		return [
			[['description'], 'filter', 'filter' => 'strip_tags'],
			[['description'], 'filter', 'filter' => 'trim'],
			[['site_id', 'description'], 'required'],
			[['site_id', 'site_contact_id'], 'integer'],
			[['description'], 'string'],
			['site_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Site', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					Site::STATUS_INACTIVE,
					Site::STATUS_ACTIVE,
				]]);
			}],
			['site_contact_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\SiteContact', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['site_id' => $this->site_id])
				->andWhere(['in', 'status', [
					SiteContact::STATUS_INACTIVE,
					SiteContact::STATUS_ACTIVE,
				]]);
			}],
			['meter_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Meter', 'modelAttribute' => 'name', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					Meter::STATUS_INACTIVE,
					Meter::STATUS_ACTIVE,
				]]);
			}],
			['channel_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\MeterChannel', 'modelAttribute' => 'channel', 'filter' => function($model){
				return $model->joinWith(['relationMeter'])
				->andWhere([
					Meter::tableName(). '.name' => $this->meter_id,
				])
				->andWhere(['in', MeterChannel::tableName(). '.status', [
					MeterChannel::STATUS_INACTIVE,
					MeterChannel::STATUS_ACTIVE,
				]]);
			}],
			['meter_id', 'match', 'pattern' => Meter::NAME_VALIDATION_PATTERN],
			[['channel_id'], 'integer'],
			['date', 'date', 'format' => 'php:d-m-Y H:i'],
			['urgency', 'default', 'value' => Task::URGENCY_LOW],
			['urgency', 'in', 'range' => array_keys(Task::getListUrgencies()), 'skipOnEmpty' => true],
			['color', 'default', 'value' => Task::COLOR_DARK_RED],
			['color', 'in', 'range' => array_keys(Task::getListColors()), 'skipOnEmpty' => true],
			['meter_id', 'validateChannel'],
		];
	}

	public function validateChannel($attribute, $params)
	{
		$meter_id = (new Query)->select(['id'])
		->from(Meter::tableName())
		->andWhere(['name' => $this->meter_id])
		->scalar();

		$channel_id = (new Query)->select(['id'])
		->from(MeterChannel::tableName())
		->andWhere([
			'meter_id' => $meter_id,
			'channel' => $this->channel_id,
		])
		->scalar();

		$exists = Task::find()->andWhere([
			'meter_id' => $meter_id,
			'channel_id' => $channel_id,
		])
		->andWhere(['in', 'status', [
			Task::STATUS_ACTIVE,
		]])
		->exists();

		if ($exists) {
			return $this->addError($attribute, Yii::t('api.task', 'Channel has already been taken.'));
		}

		$this->meter_id = $meter_id;

		if ($channel_id != null) {
			$this->channel_id = $channel_id;
		}
	}

	public function attributeLabels()
	{
		return [
			'site_id' => Yii::t('api.task', 'Site ID'),
			'site_contact_id' => Yii::t('api.task', 'Site contact ID'),
			'meter_id' => Yii::t('api.task', 'Meter ID'),
			'channel_id' => Yii::t('api.task', 'Channel ID'),
			'description' => Yii::t('api.task', 'Description'),
			'urgency' => Yii::t('api.task', 'Urgency'),
			'date' => Yii::t('api.task', 'Date'),
			'color' => Yii::t('api.task', 'Color'),
		];
	}
}
