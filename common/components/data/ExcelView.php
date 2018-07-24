<?php

namespace common\components\data;

use Yii;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use yii\helpers\ArrayHelper;

use common\helpers\FileHelper;
use common\components\i18n\LanguageSelector;

/**
 * ExcelView is the base class for view excel model.
 */
class ExcelView
{
	private $_model;
	private $_params = [];
	private $_objPHPExcel;

	/**
	 * Render excel to file
	 *
	 * @param string $filename
	 */
	public function file($filename)
	{
		$objWriter = \PHPExcel_IOFactory::createWriter($this->generateObjPHPExcel(), 'Excel5');

		// header('Content-Type: application/vnd.ms-excel');
		// header('Content-Disposition: attachment;filename="your_name.xls"');
		// header('Cache-Control: max-age=0');

		// $objWriter->save('php://output');
		// pa(1, 1);
		
		return $objWriter->save(Yii::getAlias($filename));
	}

	/**
	 * Get objPHPExcel
	 */
	public function getObjPHPExcel()
	{
		if ($this->_objPHPExcel == null) {
			$this->_objPHPExcel = new \PHPExcel();
		}

		return $this->_objPHPExcel;
	}

	/**
	 * Generate objPHPExcel
	 */
	public function generateObjPHPExcel()
	{
		$this->setObjPHPExcelAttribute();
		$objPHPExcel = $this->getObjPHPExcel();
		$direction = LanguageSelector::getAliasLanguageDirection();

		switch ($direction) {
			case LanguageSelector::DIRECTION_RTL:
				$objPHPExcel->getActiveSheet()->setRightToLeft(true);
				break;
			
			default:
				break;
		}

		
		return $objPHPExcel;
	}

	/**
	 * Set additional attributes to ObjPHPExcel
	 */
	public function setObjPHPExcelAttribute(){
		$objPHPExcel = $this->getObjPHPExcel();
		$objPHPExcelActiveSheet = $objPHPExcel->getActiveSheet();
	}

	/**
	 * Returns an excel column name.
	 *
	 * @param int $index the column index number
	 */
	public static function columnName($index)
	{
		$i = $index - 1;
		
		if ($i >= 0 && $i < 26) {
			return chr(ord('A') + $i);
		}
		
		if ($i > 25) {
			return (self::columnName($i / 26)) . (self::columnName($i % 26 + 1));
		}

		return 'A';
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
}