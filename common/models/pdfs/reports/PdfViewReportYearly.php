<?php

namespace common\models\pdfs\reports;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

use common\models\Report;
use common\components\data\PdfView;
use common\models\helpers\reports\ReportGeneratorYearly;

/**
 * PdfViewReportYearly is the class for view report yearly pdf.
 */
class PdfViewReportYearly extends PdfView
{
	/**
	 * @inheritdoc
	 */
	public function getDefaultHtmlContent()
	{
		$model = $this->getModel();
		$view = Yii::$app->getView()->render(($model->level == Report::LEVEL_SITE) ? '@common/views/report/pdf/yearly/view_site' : '@common/views/report/pdf/yearly/view_tenant', $this->getParams());
		return $view;
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultOptions()
	{
		return [
			'marginLeft' => 5,
			'marginRight' => 5,
			'marginTop' => 35,
			'marginBottom' => 35,
			'marginHeader' => 5,
			'marginFooter' => 22,
			'content' => $this->getHtmlContent(),
			'methods' => [
				'header' => $this->getHtmlHeader(),
				'footer' => $this->getHtmlFooter(),
			],
			'options' => [
				'title' => Yii::t('common.view', 'Yearly report'),
			],
			'cssInline' => '@page{
				footer: html_HTMLFooter;
				background-image:url("' .Yii::$app->urlManagerBackend->createAbsoluteUrl(['images/pdf/report/bg.jpg']). '");
				background-image-resize:6;
			}', 
		];
	}
}