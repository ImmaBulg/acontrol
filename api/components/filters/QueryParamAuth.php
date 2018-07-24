<?php

namespace api\components\filters;

use Yii;
use yii\web\UnauthorizedHttpException;
use yii\web\ForbiddenHttpException;

use common\models\ApiKey;

/**
 * QueryParamAuth is an action filter that supports the authentication based on the api token passed through a query parameter.
 */
class QueryParamAuth extends \yii\filters\auth\QueryParamAuth
{
	public $tokenParam = 'api_key';

	public function authenticate($user, $request, $response)
	{
		if (Yii::$app->getErrorHandler()->exception !== null) {
			return false;
		}

		$accessToken = $request->get($this->tokenParam);
		
		if (is_string($accessToken)) {
			$model = ApiKey::findOne([
				'api_key' => $accessToken,
				'status' => ApiKey::STATUS_ACTIVE,
			]);

			return $model;
		}

		if ($accessToken !== null) {
		   $this->handleFailure($response);
		}

		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function handleFailure($response)
	{
		throw new UnauthorizedHttpException(Yii::t('api.components', 'You are requesting with an invalid api key.'));
	}
}
