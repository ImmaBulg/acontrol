<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\Meter;
use common\models\MeterChannel;
use common\models\SiteMeterTree;

/**
 * FormSiteMeterTree is the class for site meter tree create/edit.
 */
class FormSiteMeterTree extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_site_id;

	public $tree;

	public function rules()
	{
		return [
			[['tree'], 'filter', 'filter' => 'strip_tags'],
			[['tree'], 'filter', 'filter' => 'trim'],
			[['tree'], 'validateTree'],
		];
	}

	public function validateTree($attribute, $params)
	{
		$decoded = json_decode($this->$attribute);
		$valid = (json_last_error() === JSON_ERROR_NONE && (is_object($decoded) || is_array($decoded)));

		if (!$valid) {
			return $this->addError($attribute, Yii::t('backend.rate', '{attribute} is invalid.', [
				'attribute' => $this->getAttributeLabel($attribute),
			]));
		}
	}

	public function attributeLabels()
	{
		return [
			'tree' => Yii::t('backend.meter', 'Tree'),
		];
	}

	public function loadAttributes($model)
	{
		$this->_site_id = $model->id;
	}

	public function save()
	{
		if (!$this->validate()) return false;
		if ($this->tree == null) return true;

		$transaction = Yii::$app->db->beginTransaction();
		
		try	{
			SiteMeterTree::deleteAll();
			$tree = json_decode($this->tree);

			foreach ($tree as $element) {
				$this->buildTree($element);
			}

			$transaction->commit();
			return true;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}

	private function buildTree($element, $parent_id = null)
	{
		$model_meter_channel = MeterChannel::findOne($element->id);

		$model = new SiteMeterTree();
		$model->site_id = $this->_site_id;
		$model->meter_id = $model_meter_channel->relationMeter->id;
		$model->meter_channel_id = $element->id;

		if ($parent_id) {
			$model_parent_meter_channel = MeterChannel::findOne($parent_id);
			$model->parent_meter_id = $model_parent_meter_channel->relationMeter->id;
		}
		
		$model->parent_meter_channel_id = $parent_id;

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		if (!empty($element->children)) {
			foreach ($element->children as $children) {
				$this->buildTree($children, $element->id);
			}
		}
	}
}
