<?php
/**
 * Created by PhpStorm.
 * User: Sharapov A. <alexander@sharapov.biz>
 * Web: http://sharapov.biz
 * Date: 14.09.2017
 * Time: 21:24
 */

namespace common\models;

use Yii;
use common\components\db\ActiveRecord;

/**
 * SiteCopHourly is the class for the table "site_cop_hourly".
 * @property $cop
 * @property $datetime
 * @property $tenant_id
 * @property $site_id
 */
class SiteCopHourly extends ActiveRecord {
  public static function tableName() {
    return 'site_cop_hourly';
  }

  public function rules() {
    return [
      [ [ 'cop' ], 'number' ],
      [ [ 'tenant_id' ], 'integer' ],
      [ [ 'site_id' ], 'integer' ],
    ];
  }

  public function attributeLabels() {
    return [
      'cop'       => Yii::t( 'common.rule', 'COP' ),
      'tenant_id' => Yii::t( 'common.rule', 'Tenant' ),
      'site_id'   => Yii::t( 'common.rule', 'Site ID' )
    ];
  }

  public function behaviors() {
    return [

    ];
  }
}