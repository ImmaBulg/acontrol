<?php
namespace backend\models\searches\models;

use Yii;

use common\components\i18n\Formatter;

/**
 * MeterChannelGroup search model.
 */
class MeterChannelGroup extends \common\models\MeterChannelGroup
{
	public $user_name;
	public $site_name;
	public $meter_name;
	public $group_channels;

	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['name', 'meter_name', 'user_name', 'site_name'], 'string'],
			[['created_at'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			[['group_channels'], 'integer'],
		];
	}
}
