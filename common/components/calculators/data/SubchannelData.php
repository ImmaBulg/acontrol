<?php

namespace common\components\calculators\data;

use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 24.07.2017
 * Time: 20:43
 */
class SubchannelData extends TaozRawData {


  /**
   * SubchannelData constructor.
   *
   * @param Carbon $start_date
   * @param Carbon $end_date
   * @param float  $pisga_consumption
   * @param float  $geva_consumption
   * @param float  $shefel_consumption
   * @param float  $pisga_reading
   * @param float  $geva_reading
   * @param float  $shefel_reading
   * @param        $reading_from
   * @param        $reading_to
   */
  public function __construct(
    Carbon $start_date,
    Carbon $end_date,
    float $pisga_consumption,
    float $geva_consumption,
    float $shefel_consumption,
    float $pisga_reading,
    float $geva_reading,
    float $shefel_reading,
    float $reading_from,
    float $reading_to
  ) {
    parent::__construct( $start_date, $end_date );
    $this->pisga_consumption  = $pisga_consumption;
    $this->geva_consumption   = $geva_consumption;
    $this->shefel_consumption = $shefel_consumption;
    $this->pisga_reading      = $pisga_reading;
    $this->geva_reading       = $geva_reading;
    $this->shefel_reading     = $shefel_reading;
    $this->reading_from       = $reading_from;
    $this->reading_to         = $reading_to;
  }


}