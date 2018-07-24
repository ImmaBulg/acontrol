<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 11.08.2017
 * Time: 14:10
 */

namespace common\components\calculators\data;
class MonthlyData extends TaozRawData
{
    public function add(float $shefel, float $geva, float $pisga) {
        $this->shefel_consumption += $shefel;
        $this->geva_consumption += $geva;
        $this->pisga_consumption += $pisga;
    }
}