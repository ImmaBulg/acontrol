<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 09.08.2017
 * Time: 16:10
 */

namespace common\components\calculators;

use Carbon\Carbon;
use common\components\calculators\data\SiteMainMetersData;
use common\models\AirMeterRawData;
use common\models\ElectricityMeterRawData;
use common\models\Meter;
use common\models\SiteCopHourly;
use common\models\Tenant;
use yii\db\Expression;
use yii\db\Query;

class CopTenantCalculatorHourly {
  /**
   * @var SiteMainMetersData
   */
  private $site_main_meters_data;
  private $from_date;
  private $to_date;
  private $tenant;


  /**
   * CopCalculator constructor.
   *
   * @param Carbon $from_date
   * @param Carbon $to_date
   */
  public function __construct( Carbon $from_date, Carbon $to_date ) {
    $this->from_date = clone $from_date;
    $this->to_date   = clone $to_date;
    $this->normalizeDates();
  }


  public function normalizeDates() {
    if ( $this->to_date->hour == 23 && $this->to_date->minute == 59 && $this->to_date->second == 59 ) {
      $this->to_date->addDay()->startOfDay();
    } else {
      $this->to_date->minute( 0 )->second( 0 );
    }
  }

  public function calculate( Tenant $tenant ) {
    $this->tenant = $tenant;

    $this->site_main_meters_data = new SiteMainMetersData( $this->tenant->relationSite->getMainSubChannels( Meter::TYPE_AIR ),
                                                           $this->tenant->relationSite->getMainSubChannels( Meter::TYPE_ELECTRICITY ) );

    $date_range_query_single = new DateRangeQuerySingle(
      ( new Query() )->select( [ 'datetime', 'kilowatt_hour' ] )
                     ->from( AirMeterRawData::tableName() )
                     ->andWhere( ( new Expression( 'datetime >= :from_date AND datetime <= :to_date', [
                       ':from_date' => $this->from_date->format( 'Y-m-d H:i:s' ),
                       ':to_date'   => $this->to_date->format( 'Y-m-d H:i:s' )
                     ] ) ) )
                     ->orderBy( 'datetime DESC' )
    );

    $air_sum_hourly = $electrical_sum_hourly = [];

    foreach ( $this->site_main_meters_data->getAirMainChannels() as $air_meter_channel ) {
      $consumption = $air_meter_channel->getAirConsumptionHourly( $date_range_query_single );
      foreach ( $consumption as $ts => $cns ) {
        $air_sum_hourly[ $ts ]['cns'] += $cns['consumption'];
        $air_sum_hourly[ $ts ]['meter'] = 'Air MeterID '.$air_meter_channel->getMeterName()." (Channel ".$air_meter_channel->getChannel().")";
        $air_sum_hourly[ $ts ]['readings'] = $cns['reading'];
      }
    }

    $date_range_query_single = new DateRangeQuerySingle(
      ( new Query() )->select( '(reading_shefel + reading_geva + reading_pisga) as reading_sum, date' )
                     ->from( ElectricityMeterRawData::tableName() )
                     ->andWhere( ( new Expression( 'date >= :from_date AND date <= :to_date', [
                       ':from_date' => $this->from_date->getTimestamp(),
                       ':to_date'   => $this->to_date->getTimestamp()
                     ] ) ) )
                     ->orderBy( 'date DESC' )
    );

    foreach ( $this->site_main_meters_data->getElectricalMainChannels() as $electrical_main_channel ) {
      $consumption = $electrical_main_channel->getElectricalConsumptionHourly( $date_range_query_single );
      foreach ( $consumption as $ts => $cns ) {
        $electrical_sum_hourly[ $ts ]['cns'] += $cns['consumption'];
        $electrical_sum_hourly[ $ts ]['meter'] = 'Electrical MeterID '.$electrical_main_channel->getMeterName()." (Channel ".$electrical_main_channel->getChannel().")";
        $electrical_sum_hourly[ $ts ]['readings'] = $cns['reading'];
      }
    }

    $cop_hourly = [];
    foreach ( $this->getDateRange() as $ts ) {
        $row = SiteCopHourly::findOne( [
                                         'datetime'  => date( 'Y-m-d-H-i-s', $ts ),
                                         'tenant_id' => $this->tenant->id,
                                         'site_id'   => $this->tenant->site_id
                                       ] );
        if ( $row ) {
          $cop_hourly[$ts] = $row['cop'];
        } else {
          if ( ($air_sum_hourly[ $ts ]['cns']  > 0) and ($electrical_sum_hourly[ $ts ]['cns']  > 0) ) {
            $value = (float)$air_sum_hourly[ $ts ]['cns'] / (float) $electrical_sum_hourly[ $ts ]['cns'];
            if ($value > 0 ) {
              $cop            = new SiteCopHourly();
              $cop->cop       = $value;
              $cop->datetime  = date( 'Y-m-d-H-i-s', $ts );
              $cop->tenant_id = $this->tenant->id;
              $cop->site_id   = $this->tenant->site_id;
              $cop->save();
              $cop_hourly[$ts] = $value;
            }
          }
        }
    }

    return $cop_hourly;
  }

  private function getDateRange() {
    $timestamp = [];
    for ( $date = $this->from_date; $date->lte( $this->to_date ); $date->addHour() ) {
      $timestamp[] = $date->getTimestamp();
    }

    return $timestamp;
  }
}