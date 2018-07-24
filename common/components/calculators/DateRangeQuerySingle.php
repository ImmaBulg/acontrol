<?php
/**
 * Created by PhpStorm.
 * User: Sharapov A. <alexander@sharapov.biz>
 * Web: http://sharapov.biz
 * Date: 14.09.2017
 * Time: 17:16
 */

namespace common\components\calculators;

use yii\db\Query;

class DateRangeQuerySingle {
  /**
   * @var Query
   */
  private $range_query;

  /**
   * DateRangeQuerySingle constructor.
   *
   * @param $range_query
   */
  public function __construct( Query $range_query ) {
    $this->range_query = $range_query;
  }


  public function getRangeQuery() {
    return $this->range_query;
  }
}