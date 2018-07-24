<?php

namespace common\models\pdfs\reports;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

use kartik\mpdf\Pdf;

use common\components\data\PdfView;
use common\models\helpers\reports\ReportGeneratorSummaryPerSite;

/**
 * PdfViewReportSummaryPerSite is the class for view report summary per site pdf.
 */
class PdfViewReportSummaryPerSite extends PdfView
{
	/**
	 * @inheritdoc
	 */
	public function getDefaultHtmlContent()
	{
		$view = Yii::$app->getView()->render('@common/views/report/pdf/summary-per-site/view', $this->getParams());
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
			'marginFooter' => 15,
			'orientation' => Pdf::ORIENT_LANDSCAPE,
			'content' => $this->getHtmlContent(),
			'methods' => [
				'header' => $this->getHtmlHeader(),
				'footer' => $this->getHtmlFooter(),
			],
			'options' => [
				'title' => Yii::t('common.view', 'Summary report'),
			],
			'cssInline' => '@page{
				footer: html_HTMLFooter;
				background-image:url("' .Yii::$app->urlManagerBackend->createAbsoluteUrl(['images/pdf/report/bg-horizontal.jpg']). '");
				background-image-resize:6;
			}', 
		];
	}
}