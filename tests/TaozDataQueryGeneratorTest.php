<?php

namespace tests;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 21.07.2017
 * Time: 13:58
 */
use Carbon\Carbon;
use common\models\TaozDataQueryGenerator;
use PHPUnit\Framework\TestCase;

class TaozDataQueryGeneratorTest extends TestCase
{
    public function testGenerateDayToDatesMap() {
        $generator =
            new TaozDataQueryGenerator(Carbon::createFromDate(2017, 7, 21), Carbon::createFromDate(2017, 7, 25));
        $days_map =
            $generator->generateDayToDatesMap(Carbon::createFromDate(2017, 7, 10), Carbon::createFromDate(2017, 7, 23));
        $this->assertEquals($days_map, [
            'Monday' => [
                Carbon::createFromDate(2017, 07, 10),
                Carbon::createFromDate(2017, 07, 17),
            ],
            'Tuesday' => [
                Carbon::createFromDate(2017, 07, 11),
                Carbon::createFromDate(2017, 07, 18),
            ],
            'Wednesday' => [
                Carbon::createFromDate(2017, 07, 12),
                Carbon::createFromDate(2017, 07, 19),
            ],
            'Thursday' => [
                Carbon::createFromDate(2017, 07, 13),
                Carbon::createFromDate(2017, 07, 20),
            ],
            'Friday' => [
                Carbon::createFromDate(2017, 07, 14),
                Carbon::createFromDate(2017, 07, 21),
            ],
            'Saturday' => [
                Carbon::createFromDate(2017, 07, 15),
                Carbon::createFromDate(2017, 07, 22),],
            'Sunday' => [
                Carbon::createFromDate(2017, 07, 16),
                Carbon::createFromDate(2017, 07, 23),
            ],
        ]);
    }
}