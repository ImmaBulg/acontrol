<?php

use yii\db\Query;
use yii\db\Schema;
use common\models\Site;
use common\models\SiteIpAddress;

class m170125_073850_fill_table_site_ip_address extends \common\components\db\Migration
{
	public function up()
	{
		$rows = (new Query())->select(['id', 'ip_address'])->from(Site::tableName())->where([
			'and',
			'ip_address IS NOT NULL',
			['!=', 'ip_address', ''],
		])->all();

		foreach ($rows as $row) {
			if (($model = SiteIpAddress::findOne(['site_id' => $row['id']])) == null) {
				$model = new SiteIpAddress();
				$model->site_id = $row['id'];
			}
			
			$model->ip_address = $row['ip_address'];
			$model->is_main = true;
			$model->save();
		}
	}

	public function down()
	{
		
	}
}
