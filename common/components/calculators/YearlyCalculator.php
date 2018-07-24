<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 11.08.2017
 * Time: 14:04
 */

namespace common\components\calculators;

use Carbon\Carbon;
use common\components\calculators\data\MonthlyData;
use common\components\calculators\data\YearlyData;
use common\helpers\KwhCalculator;
use common\models\Meter;
use common\models\Tenant;

class YearlyCalculator
{
    private $from_date = null;
    private $to_date = null;

    private $tenant = null;


    /**
     * YearlyCalculator constructor.
     * @param Carbon $from_date
     * @param Carbon $to_date
     * @param Tenant $tenant
     */
    public function __construct(Carbon $from_date, Carbon $to_date, Tenant $tenant) {
        $this->from_date = clone $from_date;
        $this->to_date = clone $to_date;
        $this->tenant = $tenant;
    }


    public static function instance(Carbon $from_date, Carbon $to_date, $tenant) {
        return new YearlyCalculator($from_date, $to_date, $tenant);
    }


    public function calculate() {
        $from_date = $this->from_date->subMonth(12)->startOfMonth();
        $yearly = new YearlyData($from_date, $this->to_date);
        while($from_date <= $this->to_date) {
            $start = $from_date;
            $end = $start->endOfMonth();
            if($end > $this->to_date) {
                $end = $this->to_date;
            }
            KwhCalculator::$data_usage_method = Meter::DATA_USAGE_METHOD_IMPORT;
            $monthly_data = new MonthlyData($from_date, $end);
            /**
             * Build single channel rules data
             */
            $single = KwhCalculator::buildSingleChannelRules($this->tenant, $start, $end);
            if(!empty($single)) {
                $monthly_data->add($single['shefel'], $single['geva'], $single['pisga']);
            }
            /**
             * Build group load rules data
             */
            $group = KwhCalculator::buildGroupLoadRules($this->tenant, $start, $end);
            if(!empty($group)) {
                $monthly_data->add($single['shefel'], $single['geva'], $single['pisga']);
            }
            /**
             * Build fixed load rules data
             */
            $fixed = KwhCalculator::buildFixedLoadRules($this->tenant, $start, $end);
            if(!empty($fixed)) {
                $monthly_data->add($single['shefel'], $single['geva'], $single['pisga']);
            }
            $yearly->add($monthly_data);
            $from_date->addMonth(1)->startOfMonth();
        }
        return $yearly;
    }
}