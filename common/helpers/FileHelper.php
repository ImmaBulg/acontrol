<?php

namespace common\helpers;

use Yii;
use yii\helpers\Url;
use yii\imagine\Image;

/**
 * File system helper
 */
class FileHelper extends \yii\helpers\FileHelper
{
	public static function generatePath($path)
	{
		return str_replace([
			':Y',
			':m',
			':d',
			':timestamp',
		], [
			date('Y'),
			date('m'),
			date('d'),
			time(),
		], $path);
	}

	/**
	 * Creates a new directory.
	 *
	 * @param string $path path of the directory to be created.
	 * @param integer $mode the permission to be set for the created directory.
	 * @param boolean $recursive whether to create parent directories if they do not exist.
	 * @return boolean whether the directory is created successfully
	 */
	public static function createDirectory($path, $mode = 0755, $recursive = true)
	{
		$path = self::generatePath($path);

		if (is_dir(Yii::getAlias($path))) {
			return $path;
		}
		
		$parentDir = dirname(Yii::getAlias($path));

		if ($recursive && !is_dir($parentDir)) {
			static::createDirectory($parentDir, $mode, true);
		}
		
		$result = mkdir(Yii::getAlias($path), $mode);
		chmod(Yii::getAlias($path), $mode);

		if ($result) {
			return $path;
		}
	}

	public static function saveFile($file, $path)
	{
		$pathinfo = pathinfo($file);
		$filename = self::normalizeName($pathinfo['filename']). '_' .time(). '.' .$pathinfo['extension'];

		if ($dirname = self::createDirectory($path)) {
			if ($file->saveAs(Yii::getAlias($dirname). '/' .$filename)) {
				return $path. '/' .$filename;
			}
		}
	}

	/**
	 * Delete a directory/file.
	 *
	 * @param string $path path of the directory/file to be deleted.
	 */
	public static function delete($path)
	{
		$path = Yii::getAlias($path);

		if (file_exists($path)) {
			if (is_file($path)) {
				return unlink($path);
			} elseif (is_dir($path)) {
				return rmdir($path);
			}
		}
	}

	public static function thumbnail($path, $width, $height)
	{
		if (is_file(Yii::getAlias($path))) {
			$pathinfo = pathinfo($path);
			$thumb = $pathinfo['filename']. '_' .$width. 'x' .$height. '.' .$pathinfo['extension'];
			$thumb_path = Yii::getAlias($pathinfo['dirname']. '/' .$thumb);

			if (!is_file($thumb_path)) {
				Image::thumbnail(Yii::getAlias($path), $width, $height)->save($thumb_path);
			}
			
			return Yii::$app->urlManagerStatic->createAbsoluteUrl($pathinfo['dirname']). '/' .$thumb;
		}
	}
}
