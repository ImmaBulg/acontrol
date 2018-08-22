<?php


namespace common\components\calculators\single_data;

use backend\models\searches\models\RuleFixedLoad;
use common\components\calculators\data\YearlyData;
use common\models\AirRates;
use common\models\Tenant;
use yii\helpers\VarDumper;


/**
 * Created by PhpStorm.
 * User: admin
 * Date: 03.08.2018
 * Time: 16:57
 */

class SingleTenantData extends SingleData
{
    protected $cop = 1;
    protected $consumtion;
    private $fixed_price = 0;
    private $yearly = null;
    private $pay = 0;
    private $tenant = null;
    private $rule_data = [];
    private $money_addition = 0;

    const VAT = 17;

    public function __construct($start_date, $end_date, $tenant) {
        parent::__construct($start_date, $end_date);
        $this->end_date = $end_date;
        $this->tenant = $tenant;
    }

    public function getTenant() : Tenant {
        return $this->tenant;
    }

    public function getFixedPrice() : float {
        return $this->fixed_price;
    }

    public function getPay() : float {
        return $this->pay;
    }

    public function getRuleData() : array  {
        return $this->rule_data;
    }

    public function add(SingleRuleData $data) {
        $this->rule_data[] = $data;
        $this->pay += $data->getPay();
        $this->consumtion += $data->getConsumption();
        $this->cop = $data->getCop();
        $this->fixed_price = $data->getFixedPrice();
    }

    public function getConsumptionInKwh() : float {
        return $this->consumtion * $this->cop;
    }

    public function getTotalPayWithFixed() : float {
        return $this->pay + $this->fixed_price;
    }

    public function getVat() : float {
        return $this->getTotalPayWithFixed() * self::VAT / 100;
    }

    public function getTotalPayWithVat() : float {
        return $this->getTotalPayWithFixed() + $this->getVat();
    }

    public function getYearly() {
        return $this->yearly;
    }


    public function setYearly( YearlyData $yearly_data ) {
        $this->yearly = $yearly_data;
    }

    public function calculateFixedRules()
    {
        $fixed_rules = $this->tenant->getFixedRules()->all();
        $tenant = $this->tenant;
        foreach ($this->rule_data as &$rule_data) {
            $temp = 0;
            foreach ($rule_data->getData() as $type => &$data) {
                $single_rule = $data[0]->getPay();
                $reading_summ = $data[0]->getMultipliedData()[0]->getConsumption() * $this->cop;
                if ($reading_summ != 0)
                    foreach ($fixed_rules as $rule) {
                        switch ($rule['use_type']) {
                            case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT:
                                $fixed_rule = ($single_rule * ((int)$rule['value'] / 100));
                                $data[0]->setFixedRule($fixed_rule);
                                $temp = $fixed_rule;
                                break;
                            case RuleFixedLoad::USE_TYPE_MONEY:
                                $data[0]->setFixedRule((int)$rule['value']);
                                $temp = (int)$rule['value'];
                                break;
                            case RuleFixedLoad::USE_TYPE_KWH_FIXED:
                                $rate = AirRates::getActiveWithinRangeByTypeId(
                                        $this->start_date,
                                        $this->end_date,
                                        $rule['rate_type_id']
                                    )->one();
                                $cof = $data[0]->getMultipliedData()[0]->getConsumption() / $reading_summ;
                                $value = $rule['value'] * $rate->getPrice() / 100;
                                $data[0]->setFixedRule($value);
                                $temp = $value;
                                break;
                            case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE:
                                $temp = $reading_summ * ((int)$rule['value'] / 100) * $data[0]->getPrice() / 100;
                                $data[0]->setFixedRule($temp);
                                break;

                        }
                        if (is_nan($temp))
                            //VarDumper::dump($temp . ' = ' . $reading_summ * ((int)$rule['value'] / 100 + 1), 100, true);
                    }
            }
            if (!is_nan($temp))
                $this->money_addition += $temp;
        }
    }

    public function setMoneyAddition($money_addition) : float {
        $this->money_addition = $money_addition;
    }

    public function getMoneyAddition() : float {
        return $this->money_addition;
    }

}