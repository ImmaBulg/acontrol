<?php
namespace common\models\helpers\reports;
use common\models\Site;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 26.06.2017
 * Time: 20:12
 */
interface IReportGenerator {
    public static function generate($report_from_date,$report_to_date, Site $site,$tenants = [], array $params = []);
}