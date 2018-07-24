<?php
namespace api\components;

use Yii;
use yii\web\Response;
use yii\filters\VerbFilter;

/**
 * Base controller
 */
class Controller extends \yii\rest\Controller
{
	/**
	 * Declares external actions for the controller.
	 * This method is meant to be overwritten to declare external actions for the controller.
	 * It should return an array, with array keys being action IDs, and array values the corresponding
	 * action class names or action configuration arrays. For example,
	 *
	 * ~~~
	 * return [
	 *     'action1' => 'app\components\Action1',
	 *     'action2' => [
	 *         'class' => 'app\components\Action2',
	 *         'property1' => 'value1',
	 *         'property2' => 'value2',
	 *     ],
	 * ];
	 * ~~~
	 *
	 * [[\Yii::createObject()]] will be used later to create the requested action
	 * using the configuration provided here.
	 */
	public function actions()
	{
		return [
			'error' => [
				'class' => 'api\components\actions\ErrorAction',
			],
		];
	}

	/**
	 * Returns a list of behaviors that this component should behave as.
	 *
	 * @return array the behavior configurations.
	 */
	public function behaviors()
	{
		return [
			'contentNegotiator' => [
				'class' => \yii\filters\ContentNegotiator::className(),
				'formats' => [
					'application/json' => Response::FORMAT_JSON,
					'application/xml' => Response::FORMAT_XML,
					'text/*' => Response::FORMAT_JSON, // IE9 Bug
				],
				'languages' => [
					'en',
					'he',
				],
			],
			'verbFilter' => [
				'class' => VerbFilter::className(),
				'actions' => $this->verbs(),
			],
		];
	}

	/**
	 * Declares the allowed HTTP verbs.
	 *
	 * @return array the allowed HTTP verbs.
	 */
	protected function verbs()
	{
		return [];
	}

	/**
	 * Return response errors
	 * @param string|array $value
	 */
	public function errors($value)
	{
		if (!is_array($value)) {
			$value = (array) $value;
		}

		return ['errors' => $value];
	}
}