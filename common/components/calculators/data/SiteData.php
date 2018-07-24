<?php

namespace common\components\calculators\data;

use Carbon\Carbon;
use common\models\Site;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 24.07.2017
 * Time: 21:26
 */
class SiteData extends TaozRawData
{

    public function __construct(Carbon $start_date, Carbon $end_date, Site $site) {
        parent::__construct($start_date, $end_date);
        $this->site = $site;
    }


    /**
     * @var Site
     */
    private $site = null;


    /**
     * @return Site
     */
    public function getSite(): Site {
        return $this->site;
    }


    /**
     * @var TenantData[]
     */
    private $tenant_data = [];


    /**
     * @return TenantData[]
     */
    public function getTenantData(): array {
        return $this->tenant_data;
    }


    /**
     * @var float
     */
    private $pisga_pay = 0;
    /**
     * @var float
     */
    private $geva_pay = 0;
    /**
     * @var float
     */
    private $shefel_pay = 0;


    public function add(TenantData $data) {
        $this->tenant_data[] = $data;
        $this->pisga_consumption += $data->getPisgaConsumption();
        $this->geva_consumption += $data->getGevaConsumption();
        $this->shefel_consumption += $data->getShefelConsumption();
        $this->pisga_pay += $data->getPisgaPay();
        $this->geva_pay += $data->getGevaPay();
        $this->shefel_pay += $data->getShefelPay();
    }

}