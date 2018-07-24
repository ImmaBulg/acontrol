<?php
namespace common\models\pdfs\reports;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use common\components\data\PdfView;
use common\models\helpers\reports\ReportGeneratorTenantBills;

/**
 * PdfViewReportTenantBills is the class for view report tenant bill pdf.
 */
class PdfViewReportTenantBills extends PdfView
{
    /**
     * @inheritdoc
     */
    public function getDefaultHtmlContent() {
        $view = Yii::$app->getView()->render('@common/views/report/pdf/tenant-bills/tenant-bills-view', $this->getParams());
        return $view;
    }


    /**
     * @inheritdoc
     */
    public function getDefaultOptions() {
        $options =  [
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
                'title' => Yii::t('common.view', 'Tenant bills report'),
            ],
            'cssInline' => '@page{
				footer: html_HTMLFooter;
				background-image:url("' .
                           Yii::$app->urlManagerBackend->createAbsoluteUrl(['images/pdf/report/air-bg.jpg']) . '");
				background-image-resize:6;
			}'
        ];
        return $options;
    }
}