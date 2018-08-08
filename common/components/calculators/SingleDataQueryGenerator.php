<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 06.08.2018
 * Time: 10:41
 */

namespace common\components\calculators;


use common\components\calculators\DateRangeQueryPair;
use common\constants\DataCategories;
use yii\db\Query;


class SingleDataQueryGenerator
{
    private $from_date;
    private $to_date;

    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct($from_date, $to_date) {
        $this->from_date = $from_date;
        $this->to_date = $to_date;
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
        $query_from = (clone $query)->andWhere([$attribute => $this->from_date]);
        $query_to = (clone $query)->andWhere([$attribute => $this->to_date->copy()->addDay(1)->modify('midnight')]);
        $this->queries[DataCategories::ALL][] = new DateRangeQueryPair($query_from, $query_to);
        $this->queries[DataCategories::ALL_READING][] = new DateRangeQueryPair($query_from, $query_to);
        $this->queries[DataCategories::READING_FROM] = (clone $query)->andWhere([$attribute => $this->from_date]);
        $this->queries[DataCategories::READING_TO] =
            (clone $query)->andWhere([$attribute => $this->to_date->copy()->addDay(1)->modify('midnight')]);
        return $this->queries;
    }
}