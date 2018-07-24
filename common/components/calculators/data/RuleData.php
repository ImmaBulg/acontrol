<?php

namespace common\components\calculators\data;

use Carbon\Carbon;
use common\components\calculators\ElectricalConsumptionCalculator;
use common\components\TimeRange;
use common\models\Tenant;
use common\models\RuleSingleChannel;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 21.07.2017
 * Time: 16:08
 */
class RuleData extends TaozRawData {
  private $fixed_price = 0;

  public function __construct( Carbon $start_date, Carbon $end_date, RuleSingleChannel $rule, string $regular_time_range, string $irregular_time_range ) {
    parent::__construct( $start_date, $end_date );
    self::$time_ranges[ self::REGULAR_DATA ]   = $regular_time_range;
    self::$time_ranges[ self::IRREGULAR_DATA ] = $irregular_time_range;
    $this->rule                                = $rule;
  }


  public function setCop( float $cop ) {
    $this->cop = $cop;
  }

  private static $time_ranges = [
    self::REGULAR_DATA   => null,
    self::IRREGULAR_DATA => null,
  ];


  public function getTimeRange( $type ) {
    if ( isset( self::$time_ranges[ $type ] ) ) {
      return self::$time_ranges[ $type ];
    } else {
      return null;
    }
  }


  /**
   * @return RuleSingleChannel
   */
  public function getRule(): RuleSingleChannel {
    return $this->rule;
  }


  /**
   * @return RatedData[]
   */
  public function getRegularData(): array {
    return $this->data[ self::REGULAR_DATA ];
  }


  /**
   * @return RatedData[]
   */
  public function getIrregularData(): array {
    return $this->data[ self::IRREGULAR_DATA ];
  }


  const REGULAR_DATA = 'regular_data';
  const IRREGULAR_DATA = 'irregular_data';


  public static function dataLabels() {
    return [
      self::REGULAR_DATA   => \Yii::t( 'app', 'Regular hours' ),
      self::IRREGULAR_DATA => \Yii::t( 'app', 'Irregular hours' ),
    ];
  }


  /**
   * @return array
   */
  public function getData(): array {
    return $this->data;
  }


  public static function getDataLabel( $type ) {
    if ( isset( self::dataLabels()[ $type ] ) ) {
      return self::dataLabels()[ $type ];
    } else {
      return null;
    }
  }


  private $data = [
    self::REGULAR_DATA   => [],
    self::IRREGULAR_DATA => [],
  ];
  /**
   * @var RuleSingleChannel
   */
  private $rule = null;


  /**
   * @return float
   */
  public function getPisgaPay(): float {
    return $this->pisga_pay;
  }


  /**
   * @return float
   */
  public function getShefelPay(): float {
    return $this->shefel_pay;
  }


  /**
   * @return float
   */
  public function getGevaPay(): float {
    return $this->geva_pay;
  }

  public function getPisgaReading(): float {
    return $this->pisga_reading;
  }

  public function getGevaReading(): float {
    return $this->geva_reading;
  }

  public function getShefelReading(): float {
    return $this->shefel_reading;
  }

  public function getElectricityShefelReading()
  {
      return $this->electricity_shefel_readings;
  }

  public function getElectricityPisgaReading()
  {
      return $this->electricity_pisga_readings;
  }

  public function getElectricityGevaReading()
  {
      return $this->electricity_geva_readings;
  }

  /**
   * @var float
   */
  private $pisga_pay = 0;
  /**
   * @var float
   */
  private $geva_pay = 0;
  /**
   * @var float
   */
  private $shefel_pay = 0;
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
  protected $electricity_geva_readings = 0;
  /**
   * @var float
   */
  protected $electricity_pisga_readings = 0;
  /**
   * @var float
   */
  protected $electricity_shefel_readings = 0;
  /**
   * @var float
   */
  public $cop_geva = 0;
  /**
   * @var float
   */
  public $cop_pisga = 0;
  /**
   * @var float
   */
  public $cop_shefel = 0;

  public $reading_data = array();

  public function setElectricityData(ElectricalConsumptionCalculator $calculator)
  {
      $this->electricity_geva_readings = $calculator->getGevaConsumption();
      $this->electricity_pisga_readings = $calculator->getPisgaConsumption();
      $this->electricity_shefel_readings = $calculator->getShefelConsumption();
  }

  public function addRegularData( RatedData $data ) {
    $this->data[ self::REGULAR_DATA ][] = $data;
    $this->pisga_consumption            += $data->getPisgaConsumption();
    $this->geva_consumption             += $data->getGevaConsumption();
    $this->shefel_consumption           += $data->getShefelConsumption();
    $this->pisga_reading += $data->getPisgaReading();
    $this->geva_reading += $data->getGevaReading();
    $this->shefel_reading += $data->getShefelReading();
    $this->pisga_pay                    += $data->getPisgaPay();
    $this->geva_pay                     += $data->getGevaPay();
    $this->shefel_pay                   += $data->getShefelPay();
    $this->reading_data                 = $data->reading_data;
  }


  public function addIrregularData( RatedData $data ) {
    $this->data[ self::IRREGULAR_DATA ][] = $data;
    $this->pisga_consumption              += $data->getPisgaConsumption();
    $this->geva_consumption               += $data->getGevaConsumption();
    $this->shefel_consumption             += $data->getShefelConsumption();
    $this->pisga_pay                      += $data->getPisgaPay();
    $this->geva_pay                       += $data->getGevaPay();
    $this->shefel_pay                     += $data->getShefelPay();
  }


  public function setFixedPrice( $fixed_payment ) {
    $this->fixed_price = $fixed_payment;
  }


  /**
   * @return int
   */
  public function getFixedPrice(): int {
    return $this->fixed_price;
  }

  public function setCoops(\StdClass $cop)
  {
      $this->cop_shefel = (float)$cop->shefel;
      $this->cop_geva = (float)$cop->geva;
      $this->cop_pisga = (float)$cop->pisga;
  }
}