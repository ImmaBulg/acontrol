<?php
namespace common\helpers;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 15.06.2017
 * Time: 11:04
 */
class CalculationHelper
{
    public static function isCorrectFixedPayment($fixed_payment) {
        return ($fixed_payment == 0 || !empty($fixed_payment));
    }
}