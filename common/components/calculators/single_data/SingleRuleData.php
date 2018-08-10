<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 03.08.2018
 * Time: 17:13
 */

namespace common\components\calculators\single_data;


use Carbon\Carbon;
use common\models\RuleSingleChannel;

class SingleRuleData extends SingleData
{
    private $fixed_price = 0;
    private $data = [
        self::REGULAR_DATA   => [],
        self::IRREGULAR_DATA => [],
    ];
    private $rule = null;
    private $pay = 0;
    private $reading_data = [];
    private $fixed_rule = 0;
    private static $time_ranges = [
        self::REGULAR_DATA   => null,
        self::IRREGULAR_DATA => null,
    ];

    const REGULAR_DATA = 'regular_data';
    const IRREGULAR_DATA = 'irregular_data';

    public function __construct( Carbon $start_date, Carbon $end_date, RuleSingleChannel $rule, string $regular_time_range, string $irregular_time_range ) {
        parent::__construct( $start_date, $end_date );
        self::$time_ranges[ self::REGULAR_DATA ]   = $regular_time_range;
        self::$time_ranges[ self::IRREGULAR_DATA ] = $irregular_time_range;
        $this->rule                                = $rule;
    }


    public function setCop( float $cop ) {
        $this->cop = $cop;
    }

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

    /**
     * @return RatedData[]
     */
    public function getIrregularData(): array {
        return $this->data[ self::IRREGULAR_DATA ];
    }

    public function getPay() : float {
        return $this->pay;
    }

    public function addRegularData(SingleRatedData $data) {
        $this->data[self::REGULAR_DATA][]  = $data;
        $this->pay += $data->getPay();
        $this->consumtion += $data->getConsumption();
        $this->reading += $data->getReading();
        $this->reading_data = $data;
    }

    public function addIrregularData(SingleRatedData $data) {
        $this->data[self::IRREGULAR_DATA][] = $data;
        $this->pay += $data->getPay();
        $this->consumtion += $data->getConsumption();
    }

    public function setFixedPrice($fixed_payment) {
        $this->fixed_price = $fixed_payment;
    }

    public function getFixedPrice() : float {
        return $this->fixed_price;
    }

    public function setFixedRule($fixed_rules) {
        $this->fixed_rule = $fixed_rules;
    }
}