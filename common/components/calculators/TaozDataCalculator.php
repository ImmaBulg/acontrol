<?php

namespace common\components\calculators;

use Carbon\Carbon;
use common\components\calculators\data\SubchannelData;
use common\constants\DataCategories;
use yii\db\Query;
use yii\helpers\VarDumper;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 21.07.2017
 * Time: 15:14
 */
class TaozDataCalculator {
  private $start_date = null;
  private $end_date = null;
  private $queries_by_type = [];
  public $data_by_time = [];
  private $data = [
    DataCategories::GEVA         => 0,
    DataCategories::PISGA        => 0,
    DataCategories::SHEFEL       => 0,
    DataCategories::READING_FROM => 0,
    DataCategories::READING_TO   => 0,
  ];


  /**
   * @return array|SubchannelData
   */
  public function getData(): SubchannelData {
    $data_entry =
      new SubchannelData( $this->start_date, $this->end_date,
                          $this->data[ DataCategories::PISGA ],
                          $this->data[ DataCategories::GEVA ],
                          $this->data[ DataCategories::SHEFEL ],
                          $this->data[ DataCategories::PISGA_READING ],
                          $this->data[ DataCategories::GEVA_READING ],
                          $this->data[ DataCategories::SHEFEL_READING ],
                          $this->data[ DataCategories::READING_FROM ],
                          $this->data[ DataCategories::READING_TO ] );

    return $data_entry;
  }


  /**
   * TaozDataCalculator constructor.
   *
   * @param Carbon $start_date
   * @param Carbon $end_date
   * @param array  $queries_by_type
   *
   * @internal param array $queries
   */
  public function __construct( Carbon $start_date, Carbon $end_date, array $queries_by_type ) {
    $this->start_date      = clone $start_date;
    $this->end_date        = clone $end_date;
    $this->queries_by_type = $queries_by_type;
  }


  public function calculate(float $meter_multiplier, $channel_percent ) {
    foreach ( $this->queries_by_type as $type => $queries ) {
      switch ( $type ) {
      /*  case DataCategories::SHEFEL_READING:
        case DataCategories::PISGA_READING:
        case DataCategories::GEVA_READING:
          $this->data[ $type ] =
            $this->calculateQueriesReadings( $queries );
          break;*/
        default:
            //VarDumper::dump('type: ' . $type . "\n", 100, true);
            $result = $this->calculateQueries( $queries, $type );
            $this->data[ $type ] = $result * $meter_multiplier;
      }
    }
   /* VarDumper::dump('this: ', 100, true);
    VarDumper::dump( $this->data, 100, true);
    VarDumper::dump( "\n", 100, true);*/
      //VarDumper::dump('All');
      return $this;
  }

  /**
   * @param DateRangeQueryPair[] $queries
   *
   * @return float
   */
  private function calculateQueries( $queries, $type = null ) {
    if ( $queries instanceof Query ) {
      $result = $queries->scalar();
      if ($type) {
          $this->data_by_time[$type][$queries->where[3]['datetime']->getTimestamp()] = empty($result) ? 0 : $result;
      }
      return $result;
    } else {
      if ( is_array( $queries ) ) {
        $sum = 0;
        foreach ( $queries as $query ) {
          if ( $query instanceof DateRangeQueryPair ) {
            $from_part = $query->getFromQuery()->scalar();
            $to_part   = $query->getToQuery()->scalar();
            if ( $to_part > $from_part ) {
              $result = $to_part - $from_part;
              $sum    += $result;

              if ($type && isset($result)) {
                  $this->data_by_time[$type][strtotime($query->getFromQuery()->where[3]['datetime'])] = $result;
                  //VarDumper::dump($query->getFromQuery()->where[3]['datetime'] . ' to ' . $query->getToQuery()->where[3]['datetime'] . ' = ' . $result . "\n", 3 , true);
              }
            }
          }
        }
        //VarDumper::dump($type . ' = ' . $sum);
        return $sum;
      }
    }

    return 0;
  }

  /**
   * @param DateRangeQueryPair[] $queries
   *
   * @return float
   */
  private function calculateQueriesReadings( array $queries ) {
    if ( is_array( $queries ) ) {
      $sum = 0;
      foreach ( $queries as $query ) {
        if ( $query instanceof Query ) {
          $from_part = $query->scalar();
          $sum       += $from_part;
        }
      }

      return $sum;
    }

    return 0;
  }
}