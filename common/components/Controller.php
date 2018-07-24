<?php
namespace common\components;

use Yii;

/**
 * Base controller
 */
class Controller extends \yii\web\Controller
{
	/**
	 * Go back using request referrer
	 */
	public function goBackReferrer($defaultUrl = null)
	{
		return Yii::$app->getResponse()->redirect(Yii::$app->request->referrer);
	}

	/**
	 * Render AJAX or full
	 */
	public function renderDependence($view, $params = [])
	{
		if (Yii::$app->request->isAjax) {
			return $this->getView()->renderAjax($view, $params, $this);
		} else {
			$content = $this->getView()->render($view, $params, $this);
			return $this->renderContent($content);
		}
	}
}