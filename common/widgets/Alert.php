<?php
namespace common\widgets;

use Yii;
use yii\helpers\Html;
use yii\bootstrap\BootstrapPluginAsset;

class Alert extends \yii\bootstrap\Widget
{
	private $_alert;

	const ALERT_WARNING = 'alert-warning';
	const ALERT_DANGER = 'alert-danger';
	const ALERT_INFO = 'alert-info';
	const ALERT_SUCCESS = 'alert-success';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		$session = Yii::$app->session;
		$alert = $session->getAllFlashes();

		if ($alert != null) {
			foreach ($alert as $type => $message) {
				$message = (is_array($message)) ? implode("<br />", $message) : $message;
				$this->_alert .= '<div class="alert alert-dismissible fade in ' .$type. '"><button type="button" class="close" data-dismiss="alert" aria-label="' .Yii::t('common.common', 'Close'). '"><span aria-hidden="true">×</span></button>' .$message. '</div>';
				$session->removeFlash($type);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		if ($this->_alert != null) {
			BootstrapPluginAsset::register($this->getView());
			return Html::tag('div', $this->_alert, ['class' => 'alerts']);
		}
	}
}
