<?php

namespace common\components\data;

use Yii;
use yii\helpers\ArrayHelper;
use yii\base\Model;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use kartik\mpdf\Pdf;

/**
 * PdfView is the base class for view pdf model.
 */
class PdfView extends Model
{
	private $_model;
	private $_html_content;
	private $_html_header;
	private $_html_footer;
	private $_options = [];
	private $_params = [];

	/**
	 * Render excel to file
	 *
	 * @param string $filename
	 */
	public function file($filename)
	{
		$pdf = new Pdf(ArrayHelper::merge($this->getOptions(), [
			'destination' => Pdf::DEST_FILE,
			'filename' => Yii::getAlias($filename),
            'format' => Pdf::FORMAT_A4,
		]));
		$mpdf = $pdf->api;
		$mpdf->SetHTMLHeader($this->getHtmlHeader());
		$mpdf->SetHTMLFooter($this->getHtmlFooter());
		return $pdf->render();
	}

	/**
	 * Set model
	 * @param object $value
	 */
	public function setModel($value)
	{
		$this->_model = $value;
	}

	/**
	 * Get model
	 */
	public function getModel()
	{
		return $this->_model;
	}

	/**
	 * Set html content
	 * @param string $value
	 */
	public function setHtmlContent($value)
	{
		$this->_html_content = $value;
	}

	/**
	 * Get html content
	 */
	public function getHtmlContent()
	{
		if ($this->_html_content == null) {
			$this->_html_content = $this->getDefaultHtmlContent();
		}

		return $this->_html_content;
	}

	/**
	 * Get default html content
	 */
	public function getDefaultHtmlContent()
	{
		return $this->_html_content;
	}

	/**
	 * Set html header
	 * @param string $value
	 */
	public function setHtmlHeader($value)
	{
		$this->_html_header = $value;
	}

	/**
	 * Get html header
	 */
	public function getHtmlHeader()
	{
		if ($this->_html_header == null) {
			$this->_html_header = $this->getDefaultHtmlHeader();
		}

		return $this->_html_header;
	}

	/**
	 * Get default html header
	 */
	public function getDefaultHtmlHeader()
	{
		return $this->_html_header;
	}

	/**
	 * Set html footer
	 * @param string $value
	 */
	public function setHtmlFooter($value)
	{
		$this->_html_footer = $value;
	}

	/**
	 * Get html footer
	 */
	public function getHtmlFooter()
	{
		if ($this->_html_footer == null) {
			$this->_html_footer = $this->getDefaultHtmlFooter();
		}

		return $this->_html_footer;
	}

	/**
	 * Get default html footer
	 */
	public function getDefaultHtmlFooter()
	{
		return $this->_html_footer;
	}

	/**
	 * Get options
	 */
	public function getOptions()
	{
		if ($this->_options == null) {
			$this->_options = $this->getDefaultOptions();
		}

		return $this->_options;
	}

	/**
	 * Set options
	 */
	public function setOptions($value)
	{
		$this->_options = ArrayHelper::merge($value, $this->getDefaultOptions());
	}

	/**
	 * Set default options
	 */
	public function getDefaultOptions()
	{
		return [
			'content' => $this->getHtmlContent(),
			'methods' => [
				'header' => $this->getHtmlHeader(),
				'footer' => $this->getHtmlFooter(),
			],
		];
	}

	/**
	 * Get view parameters
	 */
	public function getParams()
	{
		return $this->_params;
	}

	/**
	 * Set view parameters
	 */
	public function setParams($value)
	{
		$this->_params = $value;
	}
}