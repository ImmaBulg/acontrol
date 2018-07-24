<?php

namespace backend\models\forms;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

use common\models\User;
use common\models\UserProfile;
use common\models\Site;
use common\models\Tenant;
use common\models\Meter;
use common\components\rbac\Role;

/**
 * FormSearch is the class for search.
 */
class FormSearch extends \yii\base\Model
{
	const CLIENTS = 'clients';
	const SITES = 'sites';
	const TENANTS = 'tenants';
	const METERS = 'meters';

	public $q;
	public $type;

	public function rules()
	{
		return [
			[['q', 'type'], 'string'],
			['type', 'in', 'range' => self::getListTypes(), 'skipOnEmpty' => true],
		];
	}

	public function search()
	{
		return [
			self::CLIENTS => $this->getSearchUsers(),
			self::SITES => $this->getSearchSites(),
			self::TENANTS => $this->getSearchTenants(),
			self::METERS => $this->getSearchMeters(),
		];
	}

	private function getSearchUsers()
	{
		$t = User::tableName();
		$query = User::find()->andWhere([
			'role' => Role::ROLE_CLIENT,
		])->andWhere(['in', "$t.status", [
			User::STATUS_INACTIVE,
			User::STATUS_ACTIVE,
		]])->joinWith([
			'relationUserProfile',
		], 'LEFT JOIN');

		$query->andFilterWhere([
			'or',
			['like', "$t.name", $this->q],
			['like', "$t.email", $this->q],
			['like', UserProfile::tableName(). '.phone', $this->q],
		]);

		return new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageParam' => User::PAGE_PARAM,
				'pageSizeParam' => User::PAGE_SIZE_PARAM,
				'defaultPageSize' => User::PAGE_SIZE,
				'pageSizeLimit' => [
					User::PAGE_SIZE_LIMIT_MIN,
					User::PAGE_SIZE_LIMIT_MAX,
				],
			],
		]);
	}

	private function getSearchSites()
	{
		$t = Site::tableName();
		$query = Site::find()->andWhere(['in', "$t.status", [
			Site::STATUS_INACTIVE,
			Site::STATUS_ACTIVE,
		]])->joinWith([
			'relationUser',
		], 'LEFT JOIN');

		$query->andFilterWhere([
			'or',
			['like', "$t.name", $this->q],
			['like', "$t.electric_company_id", $this->q],
		]);

		return new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageParam' => Site::PAGE_PARAM,
				'pageSizeParam' => Site::PAGE_SIZE_PARAM,
				'defaultPageSize' => Site::PAGE_SIZE,
				'pageSizeLimit' => [
					Site::PAGE_SIZE_LIMIT_MIN,
					Site::PAGE_SIZE_LIMIT_MAX,
				],
			],
		]);
	}

	private function getSearchTenants()
	{
		$t = Tenant::tableName();
		$query = Tenant::find()->andWhere(['in', "$t.status", [
			Tenant::STATUS_INACTIVE,
			Tenant::STATUS_ACTIVE,
		]])->joinWith([
			'relationSite',
			'relationUser',
		], 'LEFT JOIN');

		$query->andFilterWhere([
			'or',
			['like', "$t.name", $this->q],
		]);

		return new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageParam' => Tenant::PAGE_PARAM,
				'pageSizeParam' => Tenant::PAGE_SIZE_PARAM,
				'defaultPageSize' => Tenant::PAGE_SIZE,
				'pageSizeLimit' => [
					Tenant::PAGE_SIZE_LIMIT_MIN,
					Tenant::PAGE_SIZE_LIMIT_MAX,
				],
			],
		]);
	}

	private function getSearchMeters()
	{
		$t = Meter::tableName();
		$query = Meter::find()->andWhere(['in', "$t.status", [
			Meter::STATUS_INACTIVE,
			Meter::STATUS_ACTIVE,
		]])->joinWith([
			'relationMeterChannels.relationRuleSingleChannels',
		], 'LEFT JOIN')->groupBy(["$t.id"]);

		$query->andFilterWhere([
			'or',
			['like', "$t.name", $this->q],
		]);

		return new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageParam' => Meter::PAGE_PARAM,
				'pageSizeParam' => Meter::PAGE_SIZE_PARAM,
				'defaultPageSize' => Meter::PAGE_SIZE,
				'pageSizeLimit' => [
					Meter::PAGE_SIZE_LIMIT_MIN,
					Meter::PAGE_SIZE_LIMIT_MAX,
				],
			],
		]);
	}

	public static function getListTypes()
	{
		return [
			self::CLIENTS,
			self::SITES ,
			self::TENANTS,
			self::METERS,
		];
	}

	public function getListUrls()
	{
		return [
			self::CLIENTS => ['/dashboard/search', 'type' => self::CLIENTS, 'q' => $this->q],
			self::SITES => ['/dashboard/search', 'type' => self::SITES, 'q' => $this->q],
			self::TENANTS => ['/dashboard/search', 'type' => self::TENANTS, 'q' => $this->q],
			self::METERS => ['/dashboard/search', 'type' => self::METERS, 'q' => $this->q],
		];		
	}

	public function getAliasUrl($type)
	{
		$list = $this->getListUrls();
		return isset($list[$type]) ? $list[$type] : false;
	}
}
