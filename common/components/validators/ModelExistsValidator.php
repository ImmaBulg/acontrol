<?php
namespace common\components\validators;

use Yii;
use yii\validators\Validator;
use yii\base\InvalidValueException;

/**
 * ModelExistsValidator validates that the attribute value is exist on model.
 */
class ModelExistsValidator extends Validator
{
	/**
	 * @var string model class
	 */
	public $modelClass;

	/**
	 * @var string model attribute
	 */
	public $modelAttribute;

	/**
	 * @var string|array|\Closure additional filter to be applied to the DB query used to check the uniqueness of the attribute value.
	 * This can be a string or an array representing the additional query condition (refer to [[\yii\db\Query::where()]]
	 * on the format of query condition), or an anonymous function with the signature `function ($query)`, where `$query`
	 * is the [[\yii\db\Query|Query]] object that you can modify in the function.
	 */
	public $filter;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		if ($this->message === null) {
			$this->message = Yii::t('common.common', '{attribute} does not exist.');
		}

		// Check predefined attributes
		if (!class_exists($this->modelClass)) {
			throw new InvalidValueException("The model class not found");
		}

		$modelClass = $this->modelClass;
		$model = new $modelClass;

		if (!Yii::$app->db->getSchema()->getTableSchema($model->tableName())->getColumn($this->modelAttribute)) {
			throw new InvalidValueException("The model attribute not found");
		}
	}

	/**
	 * @inheritdoc
	 */
	public function validateAttribute($model, $attribute)
	{
		$modelClass = $this->modelClass;
		$params = [
			$modelClass::tableName(). ".{$this->modelAttribute}" => $model->$attribute
		];
		$query = $modelClass::find();
		$query->andWhere($params);

		if ($this->filter instanceof \Closure) {
			call_user_func($this->filter, $query);
		} elseif ($this->filter !== null) {
			$query->andWhere($this->filter);
		}

		$exists = $query->exists();

		if (!$exists) {
			return $this->addError($model, $attribute, $this->message);
		}
	}
}
