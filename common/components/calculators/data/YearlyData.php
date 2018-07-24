<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 11.08.2017
 * Time: 14:10
 */

namespace common\components\calculators\data;
class YearlyData extends TaozRawData
{
    /**
     * @var MonthlyData[]
     */
    private $monthly_data = [];


    /**
     * @return MonthlyData[]
     */
    public function getMonthlyData(): array {
        return $this->monthly_data;
    }


    public function add(MonthlyData $monthly_data) {
        $this->monthly_data[] = $monthly_data;
        $this->shefel_consumption += $monthly_data->getShefelConsumption();
        $this->geva_consumption += $monthly_data->getGevaConsumption();
        $this->pisga_consumption += $monthly_data->getPisgaConsumption();
    }
}