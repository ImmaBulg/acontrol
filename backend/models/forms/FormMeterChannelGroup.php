<?php

namespace backend\models\forms;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;

use common\models\Site;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterChannelGroup;
use common\models\MeterChannelGroupItem;
use common\models\events\logs\EventLogMeterChannelGroup;

/**
 * FormMeterChannelGroup is the class for channel group create/edit.
 */
class FormMeterChannelGroup extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;

	public $user_id;
	public $site_id;
	public $meter_id;
	public $name;
	public $group_channels;

	public function rules()
	{
		return [
			[['name'], 'filter', 'filter' => 'strip_tags'],
			[['name'], 'filter', 'filter' => 'trim'],
			[['name', 'user_id', 'site_id', 'meter_id', 'group_channels'], 'required'],
			[['name'], 'string', 'max' => 255],
			['user_id', 'in', 'range' => array_keys(Site::getListUsers()), 'skipOnEmpty' => false],
			['site_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Site', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['status' => Site::STATUS_ACTIVE]);
			}],
			['meter_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Meter', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['status' => Meter::STATUS_ACTIVE]);
			}],
			['group_channels', 'validateChannels'],
		];
	}

	public function validateChannels($attribute, $params)
	{
		$values = (array) $this->$attribute;

		$count = MeterChannel::find()->where(['in', 'id', array_values($values)])
		->andWhere(['in', 'status', [
			MeterChannel::STATUS_INACTIVE,
			MeterChannel::STATUS_ACTIVE,
		]])->count();

		if (count($values) != $count) {
			return $this->addError($attribute, Yii::t('backend.meter-group', '{attribute} is invalid.', [
				'attribute' => $this->getAttributeLabel($attribute),
			]));
		}
	}

	public function attributeLabels()
	{
		return [
			'name' => Yii::t('backend.meter-group', 'Name'),
			'user_id' => Yii::t('backend.meter-group', 'Client'),
			'site_id' => Yii::t('backend.meter-group', 'Site'),
			'meter_id' => Yii::t('backend.meter-group', 'Meter ID'),
			'group_channels' => Yii::t('backend.meter-group', 'Channels in group'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_EDIT:
				$this->_id = $model->id;

				$this->user_id = $model->user_id;
				$this->site_id = $model->site_id;
				$this->meter_id = $model->meter_id;
				$this->name = $model->name;

				$group_items = $model->relationMeterChannelGroupItems;

				foreach ($group_items as $group_item) {
					$this->group_channels[] = $group_item->channel_id;
				}
				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$transaction = Yii::$app->db->beginTransaction();
		
		try	{
			$model = new MeterChannelGroup();
			$model->name = $this->name;
			$model->user_id = $this->user_id;
			$model->site_id = $this->site_id;
			$model->meter_id = $this->meter_id;

			$event = new EventLogMeterChannelGroup();
			$event->model = $model;
			$model->on(EventLogMeterChannelGroup::EVENT_AFTER_INSERT, [$event, EventLogMeterChannelGroup::METHOD_CREATE]);

			if (!$model->save()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
			}

			$group_channels = $this->group_channels;

			foreach ($group_channels as $channel) {
				$model_item = new MeterChannelGroupItem();
				$model_item->group_id = $model->id;
				$model_item->channel_id = $channel;

				if (!$model_item->save()) {
					throw new BadRequestHttpException(implode(' ', $model_item->getFirstErrors()));
				}
			}

			$transaction->commit();
			return $model;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$transaction = Yii::$app->db->beginTransaction();
		
		try	{
			$updated_attributes = [];

			$model = MeterChannelGroup::findOne($this->_id);
			$model->name = $this->name;
			$model->user_id = $this->user_id;
			$model->site_id = $this->site_id;
			$model->meter_id = $this->meter_id;

			$updated_attributes = ArrayHelper::merge($model->getUpdatedAttributes(), $updated_attributes);

			if (!$model->save()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
			}

			$group_channels = $this->group_channels;
			$model_items = $model->relationMeterChannelGroupItems;

			foreach ($model_items as $model_item) {
				if (in_array($model_item->channel_id, $group_channels)) {
					unset($group_channels[array_search($model_item->channel_id, $group_channels)]);
				} else {
					$updated_attributes = ArrayHelper::merge([
						$model_item->id => MeterChannelGroup::STATUS_DELETED,
					], $updated_attributes);
					$model_item->delete();
				}
			}

			if ($group_channels != null) {
				foreach ($group_channels as $channel) {
					$model_item = new MeterChannelGroupItem();
					$model_item->group_id = $model->id;
					$model_item->channel_id = $channel;

					$updated_attributes = ArrayHelper::merge($model_item->getUpdatedAttributes(), $updated_attributes);

					if (!$model_item->save()) {
						throw new BadRequestHttpException(implode(' ', $model_item->getFirstErrors()));
					}
				}
			}

			if ($updated_attributes != null) {
				$event = new EventLogMeterChannelGroup();
				$event->model = $model;
				$model->on(EventLogMeterChannelGroup::EVENT_INIT, [$event, EventLogMeterChannelGroup::METHOD_UPDATE]);
				$model->init();
			}

			$transaction->commit();
			return $model;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}

	public function getListGroupChannels()
	{
		$list = Meter::getListMeterChannels($this->meter_id);
		if ($this->group_channels != null) {
			foreach ($this->group_channels as $id) {
				if (!isset($list[$id])) {
					$model_channel = MeterChannel::findOne($id);

					if ($model_channel != null) {
						$list[$model_channel->id] = $model_channel->getChannelName();
					}
				}
			}
		}

		return $list;
	}
}
