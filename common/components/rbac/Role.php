<?php
namespace common\components\rbac;

use Yii;
/**
 * RBAC role component
 */
class Role extends \yii\rbac\Role
{
	const ROLE_GUEST = 'guest';
	const ROLE_CLIENT = 'client';
	const ROLE_SITE = 'site';
	const ROLE_TENANT = 'tenant';
	const ROLE_TECHNICIAN = 'technician';
	const ROLE_ADMIN = 'admin';

	public static function getListRoles()
	{
		return [
			self::ROLE_CLIENT => Yii::t('common.common', 'Client'),
			self::ROLE_TECHNICIAN => Yii::t('common.common', 'Technician'),
			self::ROLE_ADMIN => Yii::t('common.common', 'Administrator'),
			self::ROLE_SITE => Yii::t('common.common', 'Site'),
			self::ROLE_TENANT => Yii::t('common.common', 'Tenant'),
		];
	}

	public static function getListAllowedRoles()
	{
		return [
			self::ROLE_ADMIN => [
				self::ROLE_TECHNICIAN => Yii::t('common.common', 'Technician'),
				self::ROLE_CLIENT => Yii::t('common.common', 'Client'),
				self::ROLE_ADMIN => Yii::t('common.common', 'Administrator'),
				self::ROLE_SITE => Yii::t('common.common', 'Site'),
				self::ROLE_TENANT => Yii::t('common.common', 'Tenant'),
			],
		];
	}

	public static function getAliasAllowedRoles($role = null)
	{
		$list = self::getListAllowedRoles();
		
		if ($role == null && !Yii::$app instanceof yii\console\Application && !Yii::$app->user->isGuest) {
			$role = Yii::$app->user->role;
		}

		return isset($list[$role]) ? $list[$role] : [];
	}

	public static function getListDefaultRoles()
	{
		return [
			self::ROLE_GUEST => Yii::t('common.common', 'Guest'),
		];
	}
}
