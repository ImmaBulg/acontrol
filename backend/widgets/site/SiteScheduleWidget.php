<?php
namespace backend\widgets\site;

use Yii;
use yii\helpers\ArrayHelper;
use common\models\Site;
use common\models\User;
use common\models\UserOwnerSite;

class SiteScheduleWidget extends \yii\base\Widget
{
	public function init()
	{
		parent::init();
	}

	public function run()
	{	
		$search = new SearchSite();
		$data_provider = $search->search();
		$data_provider->pagination = false;
		$data_provider->sort->sortParam = 'sort-site';
		
		return $this->render('site_schedule_widget', [
			'data_provider' => $data_provider,
		]);
	}
}
