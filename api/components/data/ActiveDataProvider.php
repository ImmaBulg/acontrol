<?php

namespace api\components\data;

use Yii;
use yii\db\QueryInterface;
use yii\base\InvalidConfigException;

use common\components\db\ActiveRecord;
/**
 * ActiveDataProvider implements a data provider based on [[\yii\db\Query]] and [[\yii\db\ActiveQuery]].
 *
 * ActiveDataProvider provides data by performing DB queries using [[query]].
 *
 * The following is an example of using ActiveDataProvider to provide ActiveRecord instances:
 *
 * ~~~
 * $provider = new ActiveDataProvider([
 *     'query' => Post::find(),
 *     'pagination' => [
 *         'pageSize' => 20,
 *     ],
 * ]);
 *
 * // get the posts in the current page
 * $posts = $provider->getModels();
 * ~~~
 *
 * And the following example shows how to use ActiveDataProvider without ActiveRecord:
 *
 * ~~~
 * $query = new Query;
 * $provider = new ActiveDataProvider([
 *     'query' => $query->from('post'),
 *     'pagination' => [
 *         'pageSize' => 20,
 *     ],
 * ]);
 *
 * // get the posts in the current page
 * $posts = $provider->getModels();
 * ~~~
 */
class ActiveDataProvider extends \yii\data\ActiveDataProvider
{
	public function prepareList()
	{
		return $this->getModels();
	}
}