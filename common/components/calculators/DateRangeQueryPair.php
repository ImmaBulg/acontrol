<?php

namespace common\components\calculators;

use yii\db\Query;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 21.07.2017
 * Time: 15:07
 */
class DateRangeQueryPair
{
    /**
     * @var Query
     */
    private $from_query;
    /**
     * @var Query
     */
    private $to_query;


    /**
     * DateRangeQueryPair constructor.
     * @param $from_query
     * @param $to_query
     */
    public function __construct(Query $from_query, Query $to_query) {
        $this->from_query = $from_query;
        $this->to_query = $to_query;
    }



    public function getToQuery() {
        return $this->to_query;
    }



    public function getFromQuery() {
        return $this->from_query;
    }
}