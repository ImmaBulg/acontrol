<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 06.08.2018
 * Time: 10:55
 */

namespace common\components\calculators;


use Carbon\Carbon;
use common\components\calculators\single_data\SingleSubchannelData;
use common\constants\DataCategories;
use yii\db\Query;
use yii\helpers\VarDumper;

class SingleDataCalculator
{
    private $start_date = null;
    private $end_date = null;
    private $queries_by_type = [];
    public $data_by_time = [];
    private $data = [
        DataCategories::ALL         => 0,
        DataCategories::ALL_READING        => 0,
        DataCategories::READING_FROM => 0,
        DataCategories::READING_TO   => 0,
    ];

    public function getData(): SingleSubchannelData {
        $data_entry =
            new SingleSubchannelData( $this->start_date, $this->end_date,
                $this->data[ DataCategories::ALL ],
                $this->data[ DataCategories::ALL_READING ],
                $this->data[ DataCategories::READING_FROM ],
                $this->data[ DataCategories::READING_TO ] );

        return $data_entry;
    }

    public function __construct( Carbon $start_date, Carbon $end_date, array $queries_by_type ) {
        $this->start_date      = clone $start_date;
        $this->end_date        = clone $end_date;
        $this->queries_by_type = $queries_by_type;
    }

    public function calculate( float $voltage_multiplier, float $current_multiplier, $channel_percent ) {
        foreach ( $this->queries_by_type as $type => $queries ) {
            $result = $this->calculateQueries( $queries, $type );
            $this->data[ $type ] = $result;
        }
        return $this;
    }

    private function calculateQueries( $queries, $type = null ) {
        if ( $queries instanceof Query ) {
            $result = $queries->scalar();
            if ($type) {
                $this->data_by_time[$type][$queries->where[3]['datetime']->getTimestamp()] = empty($result) ? 0 : $result;
            }
            VarDumper::dump('from ' . $queries->where[3]['datetime'] . ' to ' . $queries->where[3]['datetime'] . ' = ' . $result . "\n", 100, true);
            return $result;
        } else {
            if ( is_array( $queries ) ) {
                $sum = 0;
                foreach ( $queries as $query ) {
                    if ( $query instanceof DateRangeQueryPair ) {
                        $from_part = $query->getFromQuery()->scalar();
                        $to_part   = $query->getToQuery()->scalar();
                        if ( $to_part > $from_part ) {
                            $result = $to_part - $from_part;
                            $sum    += $result;

                            if ($type && isset($result)) {
                                $this->data_by_time[$type][strtotime($query->getFromQuery()->where[3]['datetime'])] = $result;
                            }
                        }
                    }
                }
                return $sum;
            }
        }

        return 0;
    }
}