<?php

namespace common\components\data;

use Yii;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use common\helpers\FileHelper;
use common\components\i18n\LanguageSelector;

/**
 * DatView is the base class for view dat model.
 */
class DatView
{
	private $_model;
	private $_params = [];

	/**
	 * Render dat to file
	 *
	 * @param string $filename
	 */
	public function file($filename)
	{
		$fp = fopen(Yii::getAlias($filename), 'w');
		$data = $this->generateData();
		$dataLength = (array) ArrayHelper::getValue($data, 'length');
		$dataValues = (array) ArrayHelper::getValue($data, 'values');

		if ($dataValues != null) {
			foreach ($dataValues as $dataValue) {
				$row = [];

				foreach ($dataValue as $key => $value) {
					$row[] = str_pad($value, ArrayHelper::getValue($dataLength, $key, 10));
				}
				
				fwrite($fp, implode(" ", $row) . "\n");
				// fputcsv($fp, $values, " ");
			}
		}

		return fclose($fp);
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
	 * Get parameters
	 */
	public function getParams()
	{
		return $this->_params;
	}

	/**
	 * Set parameters
	 */
	public function setParams($value)
	{
		$this->_params = $value;
	}

	/**
	 * Generate data
	 * @return array
	 */
	public function generateData()
	{
		throw new NotSupportedException(__METHOD__ . ' is not supported.');
	}
}