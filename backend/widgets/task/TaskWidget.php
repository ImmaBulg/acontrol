<?php
namespace backend\widgets\task;

use Yii;

class TaskWidget extends \yii\base\Widget
{
	public function init()
	{
		parent::init();
	}

	public function run()
	{	
		$search = new SearchTask();
		$data_provider = $search->search();
		$filter_model = $search->filter();
		$search->getQuery()->limit(5);
		$data_provider->sort->sortParam = 'sort-task';
		$data_provider->pagination = false;

		return $this->render('task_widget', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
		]);
	}
}
