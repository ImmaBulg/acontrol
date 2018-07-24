<?php

namespace common\components\calculators\data;

use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 25.07.2017
 * Time: 18:09
 */
abstract class TaozRawData implements ITaozRawData {
  /**
   * @var Carbon
   */
  protected $start_date = null;


  /**
   * TaozRawData constructor.
   *
   * @param Carbon $start_date
   * @param Carbon $end_date
   */
  public function __construct( Carbon $start_date, Carbon $end_date ) {
    $this->start_date = clone $start_date;
    $this->end_date   = clone $end_date;
  }


  /**
   * @return Carbon
   */
  public function getStartDate(): Carbon {
    return $this->start_date;
  }


  /**
   * @return Carbon
   */
  public function getEndDate(): Carbon {
    return $this->end_date;
  }


  /**
   * @var Carbon
   */
  protected $end_date = null;
  /**
   * @var float
   */
  protected $pisga_consumption = 0;
  /**
   * @var float
   */
  protected $geva_consumption = 0;

  /**
   * @var float
   */
  protected $shefel_consumption = 0;
  /**
   * @var float
   */
  protected $pisga_reading = 0;
  /**
   * @var float
   */
  protected $geva_reading = 0;

  /**
   * @var float
   */
  protected $shefel_reading = 0;
  /**
   * @var float
   */
  protected $reading_from = 0;
  /**
   * @var float
   */
  protected $reading_to = 0;

  protected $cop = 0;


  public function getCop(): float {
    return $this->cop;
  }


  /**
   * @return float
   */
  public function getReadingFrom(): float {
    return $this->reading_from;
  }


  /**
   * @return float
   */
  public function getReadingTo(): float {
    return $this->reading_to;
  }


  /**
   * @return float
   */
  public function getPisgaConsumption(): float {
    return $this->pisga_consumption;
  }


  /**
   * @return float
   */
  public function getGevaConsumption(): float {
    return $this->geva_consumption;
  }


  /**
   * @return float
   */
  public function getShefelConsumption(): float {
    return $this->shefel_consumption;
  }

  /**
   * @return float
   */
  public function getPisgaReading(): float {
    return $this->pisga_reading;
  }


  /**
   * @return float
   */
  public function getGevaReading(): float {
    return $this->geva_reading;
  }


  /**
   * @return float
   */
  public function getShefelReading(): float {
    return $this->shefel_reading;
  }

}