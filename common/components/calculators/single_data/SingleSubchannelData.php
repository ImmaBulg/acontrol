<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 06.08.2018
 * Time: 10:03
 */

namespace common\components\calculators\single_data;


class SingleSubchannelData extends SingleData
{
    public function __construct($start_date, $end_date, $consumption, $reading, $reading_from, $reading_to) {
        parent::__construct($start_date, $end_date);
        $this->consumtion = $consumption;
        $this->reading = $reading;
        $this->reading_to = $reading_to;
        $this->reading_from = $reading_from;
    }
}