<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 06.08.2018
 * Time: 10:41
 */

namespace common\components\calculators;


use common\components\calculators\DateRangeQueryPair;
use common\components\TimeRange;
use common\constants\DataCategories;
use yii\db\Query;
use Carbon\Carbon;

class SingleDataQueryGenerator
{
    private $from_date;
    private $to_date;
    private $time_boundaries = [];

    private $day_to_dates_map = [
        'Sunday' => [],
        'Monday' => [],
        'Tuesday' => [],
        'Wednesday' => [],
        'Thursday' => [],
        'Friday' => [],
        'Saturday' => [],
    ];


    private $days = [
        'Sunday' => 1,
        'Monday' => 2,
        'Tuesday' => 3,
        'Wednesday' => 4,
        'Thursday' => 5,
        'Friday' => 6,
        'Saturday' => 7,
    ];

    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    public function getDayToDatesMap(): array {
        return $this->day_to_dates_map;
    }

    public function generateDayToDatesMap(Carbon $start_date, Carbon $end_date) {
        $day_to_dates_map = $this->day_to_dates_map;
        $temp_date = $start_date->copy();
        while($temp_date <= $end_date->startOfDay()) {
            $add_day = false;
            $day = $temp_date->format('l');
            foreach ($this->time_boundaries as $range) {
                if ($range->getDayNumber() === $this->days[$day]) {
                    $add_day = true;
                    break;
                }
            }
            if ($add_day) {
                $day_to_dates_map[$day][] = $temp_date->copy();
            }

            $temp_date->addDay();
        }
        return $day_to_dates_map;
    }

    public function __construct($from_date, $to_date, $time_boundaries = []) {
        $this->from_date = $from_date;
        $this->to_date = $to_date;

        $this->time_boundaries = $time_boundaries;
        $this->day_to_dates_map = $this->generateDayToDatesMap($this->from_date,  $this->to_date);
    }

    private $queries = [
        DataCategories::ALL => [],
        DataCategories::ALL_READING => [],
        DataCategories::READING_FROM => null,
        DataCategories::READING_TO => null,
    ];

    public function generate($attribute, $query = null) {
        if ($query === null) {
            $query = new Query();
        }
        $time_ranges = $this->getTimeRanges();
        foreach (array_keys($this->days) as $day) {
            $dates = $this->day_to_dates_map[$day] ?? [];
            foreach($dates as $date) {
                foreach($time_ranges as $time_range) {
                    if ($time_range->getDay() && $time_range->getDay() != $day) {
                        continue;
                    }
                    $date_time_from =
                        $date->copy()
                            ->setTime($time_range->getStartTime()->hour, $time_range->getStartTime()->minute)
                            ->format(self::DATE_TIME_FORMAT);
                    $date_time_to = $date->copy();
                    if($time_range->getEndTime()->equalTo(Carbon::today()->endOfDay())) {
                        $date_time_to->addDay()->startOfDay();
                    }
                    else {
                        $date_time_to->setTime($time_range->getEndTime()->hour,
                            $time_range->getEndTime()->minute);

                    }
                    $date_time_to = $date_time_to->format(self::DATE_TIME_FORMAT);
                    $query_from = (clone $query)->andWhere([$attribute => $date_time_from]);
                    $query_to = (clone $query)->andWhere([$attribute => $date_time_to]);
                    $this->queries[DataCategories::ALL][] = new DateRangeQueryPair($query_from, $query_to);
                    $this->queries[DataCategories::ALL_READING][] = new DateRangeQueryPair($query_from, $query_to);
                }
            }
        }

        $this->queries[DataCategories::READING_FROM] = (clone $query)->andWhere([$attribute => $this->from_date]);
        $this->queries[DataCategories::READING_TO] =
            (clone $query)->andWhere([$attribute => $this->to_date->copy()->addDay(1)->modify('midnight')]);
        return $this->queries;
    }

    public function getTimeRanges() {
        $time_ranges = [];

        foreach($this->time_boundaries as $time_boundary) {
                $start_time = $time_boundary->getStartTime();
                $end_time = $time_boundary->getEndTime();
                $time_ranges[] = new TimeRange($start_time, $end_time, $time_boundary->getDayNumber());
        }
        /*if(empty($time_ranges)) {
            $time_ranges[] = new TimeRange($taoz_part->getStartTime(), $taoz_part->getEndTime());
        }*/
        return $time_ranges;
    }
}