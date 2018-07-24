<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 09.08.2017
 * Time: 15:11
 */

namespace common\components\calculators\data;

use common\components\calculators\DateRangeQueryPair;
use common\components\calculators\DateRangeQuerySingle;
use yii\db\Query;

class MainMetersData {
  private $meter_name = null;
  private $channel = null;


  /**
   * MainMetersData constructor.
   *
   * @param null $meter_name
   * @param null $channel
   */
  public function __construct( $meter_name, $channel ) {
    $this->meter_name = $meter_name;
    $this->channel    = $channel;
  }


  public function getConsumption( DateRangeQueryPair $date_range_query_pair ) {
    $reading_from =
      (float) ( clone $date_range_query_pair->getFromQuery() )->andWhere( [ 'meter_id' => $this->getMeterName() ] )
                                                              ->andWhere( [ 'channel_id' => $this->getChannel() ] )
                                                              ->scalar();
    $reading_to   = (float) ( clone $date_range_query_pair->getToQuery() )->andWhere( [ 'meter_id' => $this->getMeterName() ] )
                                                                          ->andWhere( [ 'channel_id' => $this->getChannel() ] )
                                                                          ->scalar();
    $consumption  = $reading_to - $reading_from;

    return $consumption;
  }

  public function getAirConsumptionHourly( DateRangeQuerySingle $date_range_query_single ) {
    $reading_hourly =
      ( clone $date_range_query_single->getRangeQuery() )
        ->andWhere( [ 'meter_id' => $this->getMeterName() ] )
        ->andWhere( [ 'channel_id' => $this->getChannel() ] );

    $consumption = [];
    $readings    = $reading_hourly->all();
    if ( count( $readings ) > 0 ) {
      foreach ( $readings as $i => $r ) {
        /*print '<pre> Air '.$r['datetime'];
        print_r($r);
        print '</pre>';
        print $this->getMeterName()." channel ".$this->getChannel().'<br />';
        print $r['kilowatt_hour'] ." - ".$readings[ $i + 1 ]['kilowatt_hour']." = ".($r['kilowatt_hour'] - $readings[ $i + 1 ]['kilowatt_hour']);*/
        $consumption[ strtotime( $r['datetime'] ) ] = [
          'reading' => $r['kilowatt_hour'],
          'consumption' => $r['kilowatt_hour'] - $readings[ $i + 1 ]['kilowatt_hour']
          ];
      }
    }

    return $consumption;
  }

  public function getElectricalConsumptionHourly( DateRangeQuerySingle $date_range_query_single ) {
    $reading_hourly =
      ( clone $date_range_query_single->getRangeQuery() )
        ->andWhere( [ 'meter_id' => $this->getMeterName() ] )
        ->andWhere( [ 'channel_id' => $this->getChannel() ] );
    $consumption    = [];

    $readings = $reading_hourly->all();

    if ( count( $readings ) > 0 ) {
      foreach ( $readings as $i => $r ) {
        //print '<pre> Electrical '.date('Y-m-d H:i:s', $r['date']);
        //print_r($r);
        //print '</pre>';
        //print_r((float)$r['reading_sum'] - (float)$readings[ $i + 1 ]['reading_sum']);
        $reading_consumption = (float)$r['reading_sum'] - (float)$readings[ $i + 1 ]['reading_sum'];

        $consumption[ $r['date'] ] = [
          'reading' => $r['reading_sum'],
          'consumption' => $reading_consumption > 0 ? $reading_consumption : 0
        ];
      }
    }

    return $consumption;
  }


  /**
   * @return null
   */
  public function getMeterName() {
    return $this->meter_name;
  }


  /**
   * @return null
   */
  public function getChannel() {
    return $this->channel;
  }
}