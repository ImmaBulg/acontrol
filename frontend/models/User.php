<?php

namespace frontend\models;

use Yii;
use yii\helpers\ArrayHelper;
use common\models\Site;
use common\models\Tenant;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\RuleSingleChannel;
use common\components\rbac\Role;

/**
 * User
 */
class User extends \common\models\User
{
	protected $_clients = false;
	protected $_sites = false;
	protected $_tenants = false;
	protected $_meters = false;
	protected $_channels = false;
	protected $_selectedClient = false;
	protected $_selectedSite = false;
	protected $_selectedTenant = false;
	protected $_selectedMeter = false;
	protected $_selectedChannel = false;

	public function getClients()
	{
		if ($this->_clients === false) {
			$query = static::find()->andWhere([static::tableName(). '.role' => Role::ROLE_CLIENT]);

			switch ($this->role) {				
				case Role::ROLE_TENANT:
					$query->joinWith(['relationTenants'])
					->andWhere(['in', Tenant::tableName(). '.id', $this->getRelationUserOwnerTenants()->select(['tenant_id'])->column()]);
					break;

				case Role::ROLE_SITE:
					$query->joinWith(['relationSites'])
					->andWhere(['in', Site::tableName(). '.id', $this->getRelationUserOwnerSites()->select(['site_id'])->column()]);
					break;

				case Role::ROLE_CLIENT:
				default:
					if ($this->isSuperClient()) {
						$query->andWhere(['in', static::tableName(). '.id', ArrayHelper::merge([$this->id], $this->getRelationUserOwners()->select(['user_id'])->column())]);
					} else {
						$query->andWhere([static::tableName(). '.id' => $this->id]);
					}
					break;
			}

			$this->_clients = $query->groupBy([static::tableName(). '.id'])->all();
		}

		return (array) $this->_clients;
	}

	public function getSitesByClientId($client_id)
	{
		if ($this->_sites === false || !isset($this->_sites[$client_id])) {
			$query = Site::find()->andWhere([Site::tableName(). '.user_id' => $client_id])
			->andWhere([Site::tableName(). '.status' => Site::STATUS_ACTIVE]);

			switch ($this->role) {				
				case Role::ROLE_TENANT:
					$query->joinWith(['relationTenants'])
					->andWhere(['in', Tenant::tableName(). '.id', $this->getRelationUserOwnerTenants()->select(['tenant_id'])->column()]);
					break;

				case Role::ROLE_SITE:
					$query->andWhere(['in', Site::tableName(). '.id', $this->getRelationUserOwnerSites()->select(['site_id'])->column()]);
					break;

				case Role::ROLE_CLIENT:
				default:
					break;
			}

			$this->_sites[$client_id] = $query->groupBy([Site::tableName(). '.id'])->all();
		}

		return (array) $this->_sites[$client_id];
	}

	public function getTenantsBySiteId($site_id)
	{
		if ($this->_tenants === false || !isset($this->_tenants[$site_id])) {
			$query = Tenant::find()->andWhere([
				'and',
				['site_id' => $site_id],
				['status' => Tenant::STATUS_ACTIVE],
			]);

			switch ($this->role) {
				case Role::ROLE_TENANT:
					$query->andWhere(['in', Tenant::tableName(). '.id', $this->getRelationUserOwnerTenants()->select(['tenant_id'])->column()]);
					break;
				
				case Role::ROLE_SITE:
				case Role::ROLE_CLIENT:
				default:
					break;
			}

			$this->_tenants[$site_id] = $query->groupBy([Tenant::tableName(). '.id'])->all();
		}

		return (array) $this->_tenants[$site_id];
	}

	public function getMetersByTenantId($tenant_id)
	{
		if ($this->_meters === false || !isset($this->_meters[$tenant_id])) {
			$query = Meter::find()
			->joinWith([
				'relationMeterType',
				'relationMeterChannels',
				'relationMeterChannels.relationRuleSingleChannels',
			])->andWhere([
				'and',
				[RuleSingleChannel::tableName(). '.tenant_id' => $tenant_id],
				[Meter::tableName(). '.status' => Tenant::STATUS_ACTIVE],
			]);

			$this->_meters[$tenant_id] = $query->groupBy([Meter::tableName(). '.id'])->all();
		}

		return (array) $this->_meters[$tenant_id];
	}

	public function getChannelsByTenantIdAndMeterId($tenant_id, $meter_id)
	{
		if ($this->_channels === false || !isset($this->_channels[$meter_id])) {
			$query = MeterChannel::find()
			->joinWith(['relationRuleSingleChannels'])
			->andWhere([
				'and',
				[RuleSingleChannel::tableName(). '.tenant_id' => $tenant_id],
				[MeterChannel::tableName(). '.meter_id' => $meter_id],
				[MeterChannel::tableName(). '.status' => Tenant::STATUS_ACTIVE],
			]);

			$this->_channels[$meter_id] = $query->groupBy([MeterChannel::tableName(). '.id'])->all();
		}

		return (array) $this->_channels[$meter_id];
	}

	public function getSelectedClient()
	{
		if ($this->_selectedClient === false) {
			if (($client_id = ArrayHelper::getValue(Yii::$app->session->get('switch'), 'client_id')) != null) {
				$this->_selectedClient = static::findOne($client_id);
			} else {
				$this->_selectedClient = reset($this->getClients());
			}
		}

		return $this->_selectedClient;
	}

	public function getSelectedSite()
	{
		if ($this->_selectedSite === false) {
			if (($site_id = ArrayHelper::getValue(Yii::$app->session->get('switch'), 'site_id')) != null) {
				$this->_selectedSite = Site::findOne($site_id);
			} else {
				$this->_selectedSite = reset($this->getSitesByClientId(ArrayHelper::getValue($this->getSelectedClient(), 'id')));
			}
		}

		return $this->_selectedSite;
	}

	public function getSelectedTenant()
	{
		if ($this->_selectedTenant === false) {
			if (($tenant_id = ArrayHelper::getValue(Yii::$app->session->get('switch'), 'tenant_id')) != null) {
				$this->_selectedTenant = Tenant::findOne($tenant_id);
			} else {
				$this->_selectedTenant = reset($this->getTenantsBySiteId(ArrayHelper::getValue($this->getSelectedSite(), 'id')));
			}
		}

		return $this->_selectedTenant;
	}

	public function getSelectedMeter()
	{
		if ($this->_selectedMeter === false) {
			if (($meter_id = ArrayHelper::getValue(Yii::$app->session->get('switch'), 'meter_id')) != null) {
				$this->_selectedMeter = Meter::findOne($meter_id);
			} else {
				$this->_selectedMeter = reset($this->getMetersByTenantId(ArrayHelper::getValue($this->getSelectedTenant(), 'id')));
			}
		}

		return $this->_selectedMeter;
	}

	public function getSelectedChannel()
	{
		if ($this->_selectedChannel === false) {
			if (($channel_id = ArrayHelper::getValue(Yii::$app->session->get('switch'), 'channel_id')) != null) {
				$this->_selectedChannel = MeterChannel::findOne($channel_id);
			} else {
				$this->_selectedChannel = reset($this->getChannelsByTenantIdAndMeterId(ArrayHelper::getValue($this->getSelectedTenant(), 'id'), ArrayHelper::getValue($this->getSelectedMeter(), 'id')));
			}
		}

		return $this->_selectedChannel;
	}
}
