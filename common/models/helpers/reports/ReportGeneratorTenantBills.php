<?php

namespace common\models\helpers\reports;

use Carbon\Carbon;
use common\components\calculators\data\SiteData;
use common\components\calculators\data\TenantData;
use common\components\calculators\TenantCalculator;
use common\helpers\KwhCalculator;
use common\helpers\TimeManipulator;
use common\models\Rate;
use common\models\RuleSingleChannel;
use common\models\Site;
use common\models\Tenant;
use components\calculators\TenantBillData;
use DateTime;
use phpDocumentor\Reflection\Types\Integer;
use PHPUnit\Framework\Constraint\Constraint;
use Yii;
use yii\helpers\ArrayHelper;

class ReportGeneratorTenantBills extends ReportGenerator implements IReportGenerator
{
    /**
     * @var \common\models\User|null
     */
    private $site_owner = null;


    /**
     * ReportGeneratorTenantBills constructor.
     * @param Carbon $from_date
     * @param Carbon $to_date
     * @param Site $site
     * @param Tenant[] $tenants
     * @param Int $report_type
     */
    public function __construct(Carbon $from_date, Carbon $to_date, Site $site, array $tenants, int $report_type) {
        $this->report_type = $report_type;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->site = $site;
        $this->tenants = $tenants;
        $this->site_owner = $site->relationUser;
        $this->normalizeDateRange();
    }


    public static function generate($report_from_date, $report_to_date, Site $site, $tenants = [], array $params = []) {
        // TODO: Implement generate() method.
    }


    /**
     * @var Integer
     */
    public $report_type = 0;

    /**
     * @var Carbon
     */
    private $from_date = null;
    /**
     * @var Carbon
     */
    private $to_date = null;
    /**
     * @var Site
     */
    private $site = null;

    /**
     * @var Tenant[]
     */
    private $tenants = [];


    private function normalizeDateRange() {
        $this->from_date = TimeManipulator::getStartOfDay($this->from_date);
        $this->to_date = TimeManipulator::getEndOfDay($this->to_date);
    }


    public function calculate() {
        $data = new SiteData($this->from_date, $this->to_date, $this->site);

        foreach($this->tenants as $tenant) {
            $tenant_calculator = new TenantCalculator($tenant, $this->from_date, $this->to_date);
            $tenant_data = $tenant_calculator->calculate($this->report_type);
            $data->add($tenant_data);
        }

        return $data;
    }
}