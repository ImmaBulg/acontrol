<?php

namespace common\components\calculators;

use Carbon\Carbon;
use common\components\TimeRange;
use common\constants\DataCategories;
use common\models\SubAirRatesTaoz;
use yii\db\Query;
use yii\helpers\VarDumper;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 21.07.2017
 * Time: 12:37
 */
class TaozDataQueryGenerator
{
    const DEFAULT_DATE_FORMAT = 'Y-m-d H:i:s';
    const SHEFEL = 'shefel';
    const PISGA = 'pisga';
    const GEVA = 'gava';
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    private $day_to_dates_map = [
        'Sunday' => [],
        'Monday' => [],
        'Tuesday' => [],
        'Wednesday' => [],
        'Thursday' => [],
        'Friday' => [],
        'Saturday' => [],
    ];

    /**
     * @var array| \common\models\SubAirRatesTaoz[]
     */
    private $taoz_parts = [];

    /**
     * @var array|TimeRange[]
     */
    private $time_boundaries = [];
    private $from_date;
    private $to_date;


    public function getTaozParts(): array {
        return $this->taoz_parts;
    }


    /**
     * @return array
     */
    public function getDayToDatesMap(): array {
        return $this->day_to_dates_map;
    }


    public function generateDayToDatesMap(Carbon $start_date, Carbon $end_date) {
        $day_to_dates_map = $this->day_to_dates_map;
        $temp_date = $start_date->copy();
        while($temp_date <= $end_date->startOfDay()) {
            $day = $temp_date->format('l');
            $day_to_dates_map[$day][] = $temp_date->copy();
            $temp_date->addDay();
        }
        return $day_to_dates_map;
    }


    /**
     * TaozDataQueryGenerator constructor.
     * @param Carbon $from_date
     * @param Carbon $to_date
     * @param array | \common\models\SubAirRatesTaoz[] $taoz_parts
     * @param array | TimeRange[] $time_boundaries
     */
    public function __construct(Carbon $from_date, Carbon $to_date, $taoz_parts = [], $time_boundaries = []) {
        $this->from_date = clone $from_date;
        $this->to_date = clone $to_date;
        $this->time_boundaries = $time_boundaries;
        $this->day_to_dates_map = $this->generateDayToDatesMap($this->from_date,  $this->to_date);
        $this->taoz_parts = $taoz_parts;
    }


    private $queries = [
        DataCategories::GEVA => [],
        DataCategories::PISGA => [],
        DataCategories::SHEFEL => [],
        DataCategories::GEVA_READING => [],
        DataCategories::PISGA_READING => [],
        DataCategories::SHEFEL_READING => [],
        DataCategories::READING_FROM => null,
        DataCategories::READING_TO => null,
    ];


    public function generate($attribute, Query $query = null) {
        if($query === null) {
            $query = new Query();
        }
        foreach($this->taoz_parts as $taoz_part) {
            if($taoz_part instanceof SubAirRatesTaoz) {
                $time_ranges = $this->getTimeRanges($taoz_part);
                $days = $taoz_part->getDaysByWeekParts();
                foreach($days as $day) {
                    /**
                     * @var Carbon[] $dates
                     */
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
                                $date_time_to->setTime($time_range->getEndTime()->hour + 1,
                                                       $time_range->getEndTime()->minute);

                            }

                            $date_time_to = $date_time_to->format(self::DATE_TIME_FORMAT);
                            $query_from = (clone $query)->andWhere([$attribute => $date_time_from]);
                            $query_to = (clone $query)->andWhere([$attribute => $date_time_to]);
                            $this->queries[$taoz_part->type][] = new DateRangeQueryPair($query_from, $query_to);
                            $this->queries[$taoz_part->type.'_reading'][] = new DateRangeQueryPair($query_from, $query_to);
                        }
                    }
                }
            }
        }
        $this->queries[DataCategories::READING_FROM] = (clone $query)->andWhere([$attribute => $this->from_date]);
        $this->queries[DataCategories::READING_TO] =
            (clone $query)->andWhere([$attribute => $this->to_date->copy()->addDay(1)->startOfDay()->subHour(1)]);
        return $this->queries;
    }


    /**
     * @param SubAirRatesTaoz $taoz_part
     * @return array|TimeRange[]
     */
    public function getTimeRanges(SubAirRatesTaoz $taoz_part) {
        $time_ranges = [];

        foreach($this->time_boundaries as $time_boundary) {
            foreach($taoz_part->getTimeRanges() as $time_range) {
                $start_time = $time_range->getStartTime();
                $end_time = $time_range->getEndTime();
                if($time_boundary->getEndTime() < $time_range->getStartTime() ||
                   $time_boundary->getStartTime() > $time_range->getEndTime()
                ) {
                    continue;
                }
                else {
                    if($time_boundary->getStartTime() > $time_range->getStartTime()) {
                        $start_time = $time_boundary->getStartTime();
                    }
                    if($time_boundary->getEndTime() < $time_range->getEndTime()) {
                        $end_time = $time_boundary->getEndTime();
                    }
                    $time_ranges[] = new TimeRange($start_time, $end_time, $time_boundary->getDayNumber());
                }
            }
        }
        if(empty($time_ranges)) {
            $time_ranges[] = new TimeRange($taoz_part->getStartTime(), $taoz_part->getEndTime());
        }
        return $time_ranges;
    }


}