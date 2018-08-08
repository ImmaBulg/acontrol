<?php

namespace common\components\calculators\single_data;


use common\models\Site;

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 03.08.2018
 * Time: 16:52
 */

class SingleSiteData extends SingleData
{

    private $site = null;
    private $tenant_data = [];
    private $pay = 0;

    public function __construct($start_date, $end_date, $site) {
        parent::__construct($start_date, $end_date);
        $this->site = $site;
    }

    public function getSite() : Site {
        return $this->site;
    }


    public function getTenantData() : array  {
        return $this->tenant_data;
    }

    public function add(SingleTenantData $data) {
        $this->tenant_data[] = $data;
        $this->consumtion += $data->getConsumption();
        $this->pay += $data->getPay();
    }

}