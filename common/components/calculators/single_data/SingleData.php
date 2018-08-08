<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 06.08.2018
 * Time: 9:39
 */

namespace common\components\calculators\single_data;

use Carbon\Carbon;

class SingleData
{
    protected $start_date = null;
    protected $end_date = null;
    protected $consumtion = 0;
    protected $reading = 0;
    protected $reading_from = 0;
    protected $reading_to = 0;
    protected $cop = 0;

    public function __construct($start_date, $end_date) {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function getConsumption() : float {
        return $this->consumtion;
    }

    public function getReading() : float {
        return $this->reading;
    }

    public function getReadingFrom() : float {
        return $this->reading_from;
    }

    public function getReadingTo() : float {
        return $this->reading_to;
    }

    public function getCop() : float {
        return $this->cop;
    }

    public function getStartDate(): Carbon {
        return $this->start_date;
    }


    /**
     * @return Carbon
     */
    public function getEndDate(): Carbon {
        return $this->end_date;
    }
}