<?php

namespace common\models\dat\reports;

use common\helpers\CalculationHelper;
use common\models\Site;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

use common\models\Tenant;
use common\models\RateType;
use common\models\RuleFixedLoad;
use common\components\data\DatView;
use common\models\helpers\reports\ReportGeneratorTenantBills;

/**
 * DatViewReportTenantBills is the class for view report tenant bills dat.
 */
class DatViewReportTenantBills extends DatView {
  /**
   * @inheritdoc
   */
  public function generateData() {
    $index                   = 0;
    $generateData            = [];
    $params                  = $this->getParams();
    $data                    = ArrayHelper::getValue( $params, 'data', [] );
    $power_factor_visibility = ArrayHelper::getValue( $params, 'power_factor_visibility', Site::POWER_FACTOR_DONT_SHOW );
    $columns                 = [
      // Base
      // 'row' => 'Row',
      'tenant_id'                  => 'M#',
      'start_date'                 => 'SDate',
      'end_date'                   => 'EDate',

      // Single Shefel
      'single_shefel_reading_from' => 'Read1Start',
      'single_shefel_reading_to'   => 'Read1End',
      'single_shefel_reading_diff' => 'Read1Cons',
      'single_shefel_price'        => 'TARIFF1',
      'single_shefel_total_pay'    => 'Total1',

      // Single Geva
      'single_geva_reading_from'   => 'Read2Start',
      'single_geva_reading_to'     => 'Read2End',
      'single_geva_reading_diff'   => 'Read2Cons',
      'single_geva_price'          => 'TARIFF2',
      'single_geva_total_pay'      => 'Total2',

      // Single Pisga
      'single_pisga_reading_from'  => 'Read3Start',
      'single_pisga_reading_to'    => 'Read3End',
      'single_pisga_reading_diff'  => 'Read3Cons',
      'single_pisga_price'         => 'TARIFF3',
      'single_pisga_total_pay'     => 'Total3',

      // General TAOZ
      'general_taoz_reading_from'  => 'Read4Start',
      'general_taoz_reading_to'    => 'Read4End',
      'general_taoz_reading_diff'  => 'Read4Cons',
      'general_taoz_price'         => 'TARIFF4',
      'general_taoz_total_pay'     => 'Total4',

      // Group Shefel
      'group_shefel_reading_from'  => 'Read5Start',
      'group_shefel_reading_to'    => 'Read5End',
      'group_shefel_reading_diff'  => 'Read5Cons',
      'group_shefel_price'         => 'TARIFF5',
      'group_shefel_total_pay'     => 'Total5',

      // Group Geva
      'group_geva_reading_from'    => 'Read6Start',
      'group_geva_reading_to'      => 'Read6End',
      'group_geva_reading_diff'    => 'Read6Cons',
      'group_geva_price'           => 'TARIFF6',
      'group_geva_total_pay'       => 'Total6',

      // Group Pisga
      'group_pisga_reading_from'   => 'Read7Start',
      'group_pisga_reading_to'     => 'Read7End',
      'group_pisga_reading_diff'   => 'Read7Cons',
      'group_pisga_price'          => 'TARIFF7',
      'group_pisga_total_pay'      => 'Total7',

      // General Fixed
      'general_fixed_reading_from' => 'Read8Start',
      'general_fixed_reading_to'   => 'Read8End',
      'general_fixed_reading_diff' => 'Read8Cons',
      'general_fixed_price'        => 'TARIFF8',
      'general_fixed_total_pay'    => 'Total8',

      // Totals
      'fixed_payment'              => 'Permanent',
      'total_pay_without_vat'      => 'SubTotal',
      'vat_total_pay'              => 'VAT',
      'total_pay_with_vat'         => 'TOTAL',
      //'message' => 'Message',

    ];
    if ( in_array( $power_factor_visibility, [
      Site::POWER_FACTOR_SHOW_ADD_FUNDS,
      Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
    ] ) ) {
      $columns['power_factor'] = 'PowerFactor';
    }

    $generateData[ $index ] = $columns;

    foreach ( (array) $data as $row ) {

      $tenant    = $row['tenant'];
      $tenant_id = $tenant->id;

      if ( ! $tenant->is_visible_on_dat_file ) {
        continue;
      }


      /**
       * REPLACE / SHOW fixed_payments on rates on priority `So Rate->Tenant->Site in order of priority`
       */
      $_fp = 0;
      if ( CalculationHelper::isCorrectFixedPayment( $row['fixed_payment'] ) ) // tenant
      {
        $_fp = $row['fixed_payment'];
      } elseif ( CalculationHelper::isCorrectFixedPayment( $tenant->relationSiteBillingSetting['fixed_payment'] ) ) // site
      {
        $_fp = $tenant->relationSiteBillingSetting['fixed_payment'];
      } elseif ( CalculationHelper::isCorrectFixedPayment( $params['additional_parameters']['rates_fixed_payments'] ) ) // rate
      {
        $_fp = $params['additional_parameters']['rates_fixed_payments'];
      }
      // replace
      $row['fixed_payment'] = $_fp;
      // END REPLACE


      $fixed_payment = $row['fixed_payment'];


      $power_factor_value = $row['power_factor_value'];
      $power_factor_pay   = $row['power_factor_pay'];
      $vat_percentage     = $row['vat_percentage'] / 100;
      $total_pay          = 0;
      $message            = '';
      $rowIndex           = "$index.0";
      $fixed_added        = false;

      //****************MAIN***********************
      if ( ! empty( $row['rules'] ) ) {
        $totalIndex = "$index.0";


        //Loop for every rule of the tenant
        foreach ( $row['rules'] as $rule ) {
          $total_pay = 0;
          //Loop as many times as number of rates (1 or 2 if was rate change)

          switch ( $rule['rule']['type'] ) {
            /**
             * Single Rules
             */
            case ReportGeneratorTenantBills::RULE_SINGLE_CHANNEL:

              foreach ( $rule['rates'] as $rateIndex => $rate ) {
                $rowIndex = "$index.$rateIndex";

                switch ( $row['rate_type'] ) {
                  // taoz rate for shefel-geva-pisga - saved to TARRIF 1-2-3
                  case RateType::TYPE_TAOZ:

                    // Base
                    // $generateData[$rowIndex]['row'] = $rowIndex. $rule['rule']['type'];
                    $generateData[ $rowIndex ]['tenant_id']  = $tenant_id;
                    $generateData[ $rowIndex ]['start_date'] = Yii::$app->formatter->asDate( $rate['reading_from_date'], 'ddMMyy' );
                    $generateData[ $rowIndex ]['end_date']   = Yii::$app->formatter->asDate( $rate['reading_to_date'], 'ddMMyy' );

                    // Single Shefel
                    $generateData[ $rowIndex ]['single_shefel_reading_from'] = ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_from", 0 ) + $rate['shefel']['reading_from'];
                    $generateData[ $rowIndex ]['single_shefel_reading_to']   = ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_to", 0 ) + $rate['shefel']['reading_to'];
                    $generateData[ $rowIndex ]['single_shefel_reading_diff'] = ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_diff", 0 ) + $rate['shefel']['reading_diff'];
                    $generateData[ $rowIndex ]['single_shefel_price']        = $rate['shefel']['price'];
                    $generateData[ $rowIndex ]['single_shefel_total_pay']    = ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_total_pay", 0 ) + $rate['shefel']['total_pay'];

                    // Single Geva
                    $generateData[ $rowIndex ]['single_geva_reading_from'] = ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_from", 0 ) + $rate['geva']['reading_from'];
                    $generateData[ $rowIndex ]['single_geva_reading_to']   = ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_to", 0 ) + $rate['geva']['reading_to'];
                    $generateData[ $rowIndex ]['single_geva_reading_diff'] = ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_diff", 0 ) + $rate['geva']['reading_diff'];
                    $generateData[ $rowIndex ]['single_geva_price']        = $rate['geva']['price'];
                    $generateData[ $rowIndex ]['single_geva_total_pay']    = ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_total_pay", 0 ) + $rate['geva']['total_pay'];

                    // Single Pisga
                    $generateData[ $rowIndex ]['single_pisga_reading_from'] = ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_from", 0 ) + $rate['pisga']['reading_from'];
                    $generateData[ $rowIndex ]['single_pisga_reading_to']   = ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_to", 0 ) + $rate['pisga']['reading_to'];
                    $generateData[ $rowIndex ]['single_pisga_reading_diff'] = ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_diff", 0 ) + $rate['pisga']['reading_diff'];
                    $generateData[ $rowIndex ]['single_pisga_price']        = $rate['pisga']['price'];
                    $generateData[ $rowIndex ]['single_pisga_total_pay']    = ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_total_pay", 0 ) + $rate['pisga']['total_pay'];

                    // General TAOZ
                    $generateData[ $rowIndex ]['general_taoz_reading_from'] = 0.00;
                    $generateData[ $rowIndex ]['general_taoz_reading_to']   = 0.00;
                    $generateData[ $rowIndex ]['general_taoz_reading_diff'] = 0.00;
                    $generateData[ $rowIndex ]['general_taoz_price']        = 0.00;
                    $generateData[ $rowIndex ]['general_taoz_total_pay']    = 0.00;

                    // Group Shefel
                    $generateData[ $rowIndex ]['group_shefel_reading_from'] = 0.00;
                    $generateData[ $rowIndex ]['group_shefel_reading_to']   = 0.00;
                    $generateData[ $rowIndex ]['group_shefel_reading_diff'] = 0.00;
                    $generateData[ $rowIndex ]['group_shefel_price']        = 0.00;
                    $generateData[ $rowIndex ]['group_shefel_total_pay']    = 0.00;

                    // Group Geva
                    $generateData[ $rowIndex ]['group_geva_reading_from'] = 0.00;
                    $generateData[ $rowIndex ]['group_geva_reading_to']   = 0.00;
                    $generateData[ $rowIndex ]['group_geva_reading_diff'] = 0.00;
                    $generateData[ $rowIndex ]['group_geva_price']        = 0.00;
                    $generateData[ $rowIndex ]['group_geva_total_pay']    = 0.00;

                    // Group Pisga
                    $generateData[ $rowIndex ]['group_pisga_reading_from'] = 0.00;
                    $generateData[ $rowIndex ]['group_pisga_reading_to']   = 0.00;
                    $generateData[ $rowIndex ]['group_pisga_reading_diff'] = 0.00;
                    $generateData[ $rowIndex ]['group_pisga_price']        = 0.00;
                    $generateData[ $rowIndex ]['group_pisga_total_pay']    = 0.00;

                    // General Fixed
                    $generateData[ $rowIndex ]['general_fixed_reading_from'] = 0.00;
                    $generateData[ $rowIndex ]['general_fixed_reading_to']   = 0.00;
                    $generateData[ $rowIndex ]['general_fixed_reading_diff'] = 0.00;
                    $generateData[ $rowIndex ]['general_fixed_price']        = 0.00;
                    $generateData[ $rowIndex ]['general_fixed_total_pay']    = 0.00;

                    //Collate rule totals
                    $total_pay = $rate['shefel']['total_pay'] + $rate['geva']['total_pay'] + $rate['pisga']['total_pay'];

                    //Overwrites to first row (fixed payment' power factor)
                    if ( $rowIndex == $totalIndex ) {
                      $generateData[ $rowIndex ]['fixed_payment']         = $fixed_payment;
                      $generateData[ $rowIndex ]['total_pay_without_vat'] = ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay;
                      $generateData[ $rowIndex ]['vat_total_pay']         = ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage;
                      $generateData[ $rowIndex ]['total_pay_with_vat']    = ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage );
                      if ( in_array( $power_factor_visibility, [
                        Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                        Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                      ] ) ) {
                        $generateData[ $rowIndex ]['power_factor'] = $power_factor_value;
                      }
                    } else {
                      $generateData[ $rowIndex ]['fixed_payment']         = 0.00;
                      $generateData[ $rowIndex ]['total_pay_without_vat'] = ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay;
                      $generateData[ $rowIndex ]['vat_total_pay']         = ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage;
                      $generateData[ $rowIndex ]['total_pay_with_vat']    = ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage );
                      if ( in_array( $power_factor_visibility, [
                        Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                        Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                      ] ) ) {
                        $generateData[ $rowIndex ]['power_factor'] = $power_factor_value;
                      }
                    }
                    $total_pay = 0;

                    break;
                  // Home and General rates flat - saved to TARRIF 8
                  default:

                    // Base
                    // $generateData[$rowIndex]['row'] = $rowIndex;
                    $generateData[ $rowIndex ]['tenant_id']  = $tenant_id;
                    $generateData[ $rowIndex ]['start_date'] = Yii::$app->formatter->asDate( $rate['reading_from_date'], 'ddMMyy' );
                    $generateData[ $rowIndex ]['end_date']   = Yii::$app->formatter->asDate( $rate['reading_to_date'], 'ddMMyy' );

                    // Single Shefel
                    $generateData[ $rowIndex ]['single_shefel_reading_from'] = 0.00;
                    $generateData[ $rowIndex ]['single_shefel_reading_to']   = 0.00;
                    $generateData[ $rowIndex ]['single_shefel_reading_diff'] = 0.00;
                    $generateData[ $rowIndex ]['single_shefel_price']        = 0.00;
                    $generateData[ $rowIndex ]['single_shefel_total_pay']    = 0.00;

                    // Single Geva
                    $generateData[ $rowIndex ]['single_geva_reading_from'] = 0.00;
                    $generateData[ $rowIndex ]['single_geva_reading_to']   = 0.00;
                    $generateData[ $rowIndex ]['single_geva_reading_diff'] = 0.00;
                    $generateData[ $rowIndex ]['single_geva_price']        = 0.00;
                    $generateData[ $rowIndex ]['single_geva_total_pay']    = 0.00;

                    // Single Pisga
                    $generateData[ $rowIndex ]['single_pisga_reading_from'] = 0.00;
                    $generateData[ $rowIndex ]['single_pisga_reading_to']   = 0.00;
                    $generateData[ $rowIndex ]['single_pisga_reading_diff'] = 0.00;
                    $generateData[ $rowIndex ]['single_pisga_price']        = 0.00;
                    $generateData[ $rowIndex ]['single_pisga_total_pay']    = 0.00;

                    // General TAOZ
                    $generateData[ $rowIndex ]['general_taoz_reading_from'] = 0.00;
                    $generateData[ $rowIndex ]['general_taoz_reading_to']   = 0.00;
                    $generateData[ $rowIndex ]['general_taoz_reading_diff'] = 0.00;
                    $generateData[ $rowIndex ]['general_taoz_price']        = 0.00;
                    $generateData[ $rowIndex ]['general_taoz_total_pay']    = 0.00;

                    // Group Shefel
                    $generateData[ $rowIndex ]['group_shefel_reading_from'] = 0.00;
                    $generateData[ $rowIndex ]['group_shefel_reading_to']   = 0.00;
                    $generateData[ $rowIndex ]['group_shefel_reading_diff'] = 0.00;
                    $generateData[ $rowIndex ]['group_shefel_price']        = 0.00;
                    $generateData[ $rowIndex ]['group_shefel_total_pay']    = 0.00;

                    // Group Geva
                    $generateData[ $rowIndex ]['group_geva_reading_from'] = 0.00;
                    $generateData[ $rowIndex ]['group_geva_reading_to']   = 0.00;
                    $generateData[ $rowIndex ]['group_geva_reading_diff'] = 0.00;
                    $generateData[ $rowIndex ]['group_geva_price']        = 0.00;
                    $generateData[ $rowIndex ]['group_geva_total_pay']    = 0.00;

                    // Group Pisga
                    $generateData[ $rowIndex ]['group_pisga_reading_from'] = 0.00;
                    $generateData[ $rowIndex ]['group_pisga_reading_to']   = 0.00;
                    $generateData[ $rowIndex ]['group_pisga_reading_diff'] = 0.00;
                    $generateData[ $rowIndex ]['group_pisga_price']        = 0.00;
                    $generateData[ $rowIndex ]['group_pisga_total_pay']    = 0.00;

                    // General Fixed
                    $generateData[ $rowIndex ]['general_fixed_reading_from'] = ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_from", 0 ) + $rate['shefel']['reading_from'] + $rate['geva']['reading_from'] + $rate['pisga']['reading_from'];
                    $generateData[ $rowIndex ]['general_fixed_reading_to']   = ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_to", 0 ) + $rate['shefel']['reading_to'] + $rate['geva']['reading_to'] + $rate['pisga']['reading_to'];
                    $generateData[ $rowIndex ]['general_fixed_reading_diff'] = ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_diff", 0 ) + $rate['pisga']['reading_diff'] + $rate['geva']['reading_diff'] + $rate['shefel']['reading_diff'];
                    $generateData[ $rowIndex ]['general_fixed_price']        = $rate['price'];
                    $generateData[ $rowIndex ]['general_fixed_total_pay']    = ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_total_pay", 0 ) + $rate['pisga']['total_pay'] + $rate['geva']['total_pay'] + $rate['shefel']['total_pay'];

                    //Collate rule totals
                    $total_pay = $rate['shefel']['total_pay'] + $rate['geva']['total_pay'] + $rate['pisga']['total_pay'];

                    //Overwrites to first row (fixed payment' power factor)
                    if ( $rowIndex == $totalIndex ) {
                      $generateData[ $rowIndex ]['fixed_payment']         = $fixed_payment;
                      $generateData[ $rowIndex ]['total_pay_without_vat'] = ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay;
                      $generateData[ $rowIndex ]['vat_total_pay']         = ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage;
                      $generateData[ $rowIndex ]['total_pay_with_vat']    = ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage );
                      if ( in_array( $power_factor_visibility, [
                        Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                        Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                      ] ) ) {
                        $generateData[ $rowIndex ]['power_factor'] = $power_factor_value;
                      }
                    } else {
                      $generateData[ $rowIndex ]['fixed_payment']         = 0.00;
                      $generateData[ $rowIndex ]['total_pay_without_vat'] = ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay;
                      $generateData[ $rowIndex ]['vat_total_pay']         = ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage;
                      $generateData[ $rowIndex ]['total_pay_with_vat']    = ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage );
                      if ( in_array( $power_factor_visibility, [
                        Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                        Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                      ] ) ) {
                        $generateData[ $rowIndex ]['power_factor'] = 0.00;
                      }
                    }
                    $total_pay = 0;
                    break;
                }

                if ( $rowIndex == $totalIndex ) {
                  if ( $fixed_added == false ) {
                    //Add fixed payment
                    $total_pay_without_vat                                = ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 );
                    $generateData[ $totalIndex ]['total_pay_without_vat'] = $total_pay_without_vat + $fixed_payment;
                    //Add power factor
                    if ( in_array( $power_factor_visibility, [ Site::POWER_FACTOR_SHOW_ADD_FUNDS ] ) ) {
                      $generateData[ $totalIndex ]['total_pay_without_vat'] = ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $power_factor_pay;
                    }
                    $fixed_added = true;
                  }
                  $generateData[ $rowIndex ]['vat_total_pay']      = ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) * $vat_percentage;
                  $generateData[ $rowIndex ]['total_pay_with_vat'] = ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) * ( 1 + $vat_percentage );
                }
              }
              break;

            /**
             * Group rules
             */
            case ReportGeneratorTenantBills::RULE_GROUP_LOAD:

              foreach ( $rule['rates'] as $rateIndex => $rate ) {
                $rowIndex = "$index.$rateIndex";

                switch ( $row['rate_type'] ) {
                  // taoz rate for shefel-geva-pisga - saved to TARRIF 1-2-3
                  case RateType::TYPE_TAOZ:

                    foreach ($generateData as $i => $b) {
                      if($b['tenant_id'] == $tenant_id
                         and
                         $b['start_date'] == Yii::$app->formatter->asDate( $rate['reading_from_date'], 'ddMMyy' )
                         and
                         $b['end_date'] == Yii::$app->formatter->asDate( $rate['reading_to_date'], 'ddMMyy' )
                      ) {
                        $rowIndex = $i;
                      }
                    }

                    // Base
                    $generateData[ $rowIndex ]['tenant_id']  = $tenant_id;
                    $generateData[ $rowIndex ]['start_date'] = Yii::$app->formatter->asDate( $rate['reading_from_date'], 'ddMMyy' );
                    $generateData[ $rowIndex ]['end_date']   = Yii::$app->formatter->asDate( $rate['reading_to_date'], 'ddMMyy' );

                    // Single Shefel
                    $generateData[ $rowIndex ]['single_shefel_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_price", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_total_pay", 0 ) );

                    // Single Geva
                    $generateData[ $rowIndex ]['single_geva_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_price", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_total_pay", 0 ) );

                    // Single Pisga
                    $generateData[ $rowIndex ]['single_pisga_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_price", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_total_pay", 0 ) );

                    // General TAOZ
                    $generateData[ $rowIndex ]['general_taoz_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_price", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_total_pay", 0 ) );

                    // Group Shefel
                    $generateData[ $rowIndex ]['group_shefel_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['group_shefel_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['group_shefel_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_diff", 0 ) + $rate['shefel']['reading_diff'] );
                    $generateData[ $rowIndex ]['group_shefel_price']        = static::getFormattedNumber( $rate['shefel']['price'] );
                    $generateData[ $rowIndex ]['group_shefel_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_total_pay", 0 ) + $rate['shefel']['total_pay'] );

                    // Group Geva
                    $generateData[ $rowIndex ]['group_geva_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['group_geva_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['group_geva_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_diff", 0 ) + $rate['geva']['reading_diff'] );
                    $generateData[ $rowIndex ]['group_geva_price']        = static::getFormattedNumber( $rate['geva']['price'] );
                    $generateData[ $rowIndex ]['group_geva_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_total_pay", 0 ) + $rate['geva']['total_pay'] );

                    // Group Pisga
                    $generateData[ $rowIndex ]['group_pisga_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['group_pisga_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['group_pisga_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_diff", 0 ) + $rate['pisga']['reading_diff'] );
                    $generateData[ $rowIndex ]['group_pisga_price']        = static::getFormattedNumber( $rate['pisga']['price'] );
                    $generateData[ $rowIndex ]['group_pisga_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_total_pay", 0 ) + $rate['pisga']['total_pay'] );

                    // General Fixed
                    $generateData[ $rowIndex ]['general_fixed_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['general_fixed_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['general_fixed_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['general_fixed_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_price", 0 ) );
                    $generateData[ $rowIndex ]['general_fixed_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_total_pay", 0 ) );

                    //Collate rule totals
                    $total_pay = $rate['shefel']['total_pay'] + $rate['geva']['total_pay'] + $rate['pisga']['total_pay'];


                    //Overwrites totals for each row
                    if ( $rowIndex == $totalIndex ) {
                      $generateData[ $rowIndex ]['fixed_payment']         = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.fixed_payment", 0 ) );
                      $generateData[ $rowIndex ]['total_pay_without_vat'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay );
                      $generateData[ $rowIndex ]['vat_total_pay']         = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage );
                      $generateData[ $rowIndex ]['total_pay_with_vat']    = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage ) );
                      if ( in_array( $power_factor_visibility, [
                        Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                        Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                      ] ) ) {
                        $generateData[ $rowIndex ]['power_factor'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.power_factor", 0 ) );
                      }
                    } else {
                      $generateData[ $rowIndex ]['fixed_payment']         = static::getFormattedNumber( 0.00 );
                      $generateData[ $rowIndex ]['total_pay_without_vat'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay );
                      $generateData[ $rowIndex ]['vat_total_pay']         = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage );
                      $generateData[ $rowIndex ]['total_pay_with_vat']    = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage ) );
                      if ( in_array( $power_factor_visibility, [
                        Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                        Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                      ] ) ) {
                        $generateData[ $rowIndex ]['power_factor'] = static::getFormattedNumber( 0.00 );
                      }
                    }
                    $total_pay = 0;
                    break;

                  // Home and General rates flat - saved to TARRIF 8
                  default:

                    // Base
                    $generateData[ $rowIndex ]['tenant_id']  = $tenant_id;
                    $generateData[ $rowIndex ]['start_date'] = Yii::$app->formatter->asDate( $rate['reading_from_date'], 'ddMMyy' );
                    $generateData[ $rowIndex ]['end_date']   = Yii::$app->formatter->asDate( $rate['reading_to_date'], 'ddMMyy' );

                    // Single Shefel
                    $generateData[ $rowIndex ]['single_shefel_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_price", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_total_pay", 0 ) );

                    // Single Geva
                    $generateData[ $rowIndex ]['single_geva_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_price", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_total_pay", 0 ) );

                    // Single Pisga
                    $generateData[ $rowIndex ]['single_pisga_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_price", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_total_pay", 0 ) );

                    // General TAOZ
                    $generateData[ $rowIndex ]['general_taoz_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_price", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_total_pay", 0 ) );

                    // Group Shefel
                    $generateData[ $rowIndex ]['group_shefel_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['group_shefel_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['group_shefel_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_diff", 0 ) + $rate['shefel']['reading_diff'] );
                    $generateData[ $rowIndex ]['group_shefel_price']        = static::getFormattedNumber( $rate['shefel']['price'] );
                    $generateData[ $rowIndex ]['group_shefel_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_total_pay", 0 ) + $rate['shefel']['total_pay'] );

                    // Group Geva
                    $generateData[ $rowIndex ]['group_geva_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['group_geva_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['group_geva_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_diff", 0 ) + $rate['geva']['reading_diff'] );
                    $generateData[ $rowIndex ]['group_geva_price']        = static::getFormattedNumber( $rate['geva']['price'] );
                    $generateData[ $rowIndex ]['group_geva_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_total_pay", 0 ) + $rate['geva']['total_pay'] );

                    // Group Pisga
                    $generateData[ $rowIndex ]['group_pisga_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['group_pisga_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['group_pisga_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_diff", 0 ) + $rate['pisga']['reading_diff'] );
                    $generateData[ $rowIndex ]['group_pisga_price']        = static::getFormattedNumber( $rate['pisga']['price'] );
                    $generateData[ $rowIndex ]['group_pisga_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_total_pay", 0 ) + $rate['pisga']['total_pay'] );

                    // General Fixed
                    $generateData[ $rowIndex ]['general_fixed_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['general_fixed_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['general_fixed_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_diff", 0 ) + $rate['pisga']['reading_diff'] + $rate['geva']['reading_diff'] + $rate['shefel']['reading_diff'] );
                    $generateData[ $rowIndex ]['general_fixed_price']        = static::getFormattedNumber( $rate['price'] );
                    $generateData[ $rowIndex ]['general_fixed_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_total_pay", 0 ) + $rate['pisga']['total_pay'] + $rate['geva']['total_pay'] + $rate['shefel']['total_pay'] );

                    //Collate rule totals
                    $total_pay = $rate['shefel']['total_pay'] + $rate['geva']['total_pay'] + $rate['pisga']['total_pay'];

                    //Overwrites totals for each row
                    if ( $rowIndex == $totalIndex ) {
                      $generateData[ $rowIndex ]['fixed_payment']         = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.fixed_payment", 0 ) );
                      $generateData[ $rowIndex ]['total_pay_without_vat'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay );
                      $generateData[ $rowIndex ]['vat_total_pay']         = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage );
                      $generateData[ $rowIndex ]['total_pay_with_vat']    = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage ) );
                      if ( in_array( $power_factor_visibility, [
                        Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                        Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                      ] ) ) {
                        $generateData[ $rowIndex ]['power_factor'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.power_factor", 0 ) );
                      }
                    } else {
                      $generateData[ $rowIndex ]['fixed_payment']         = static::getFormattedNumber( 0.00 );
                      $generateData[ $rowIndex ]['total_pay_without_vat'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay );
                      $generateData[ $rowIndex ]['vat_total_pay']         = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage );
                      $generateData[ $rowIndex ]['total_pay_with_vat']    = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage ) );
                      if ( in_array( $power_factor_visibility, [
                        Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                        Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                      ] ) ) {
                        $generateData[ $rowIndex ]['power_factor'] = static::getFormattedNumber( 0.00 );
                      }
                    }
                    $total_pay = 0;
                    break;
                }

                if ( $rowIndex == $totalIndex ) {
                  $generateData[ $rowIndex ]['vat_total_pay']      = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) * $vat_percentage );
                  $generateData[ $rowIndex ]['total_pay_with_vat'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) * ( 1 + $vat_percentage ) );
                }
              }
              break;


//TODO: Finish fixed load cases
            /**
             * Fixed rules
             */
            case ReportGeneratorTenantBills::RULE_FIXED_LOAD:
              switch ( $rule['rule']['use_type'] ) {
                case RuleFixedLoad::USE_TYPE_KWH_TAOZ: //=2
                  foreach ( $rule['rates'] as $rateIndex => $rate ) {
                    $rowIndex = "$index.$rateIndex";

                    // Base
                    $generateData[ $rowIndex ]['tenant_id']  = $tenant_id;
                    $generateData[ $rowIndex ]['start_date'] = Yii::$app->formatter->asDate( $rate['reading_from_date'], 'ddMMyy' );
                    $generateData[ $rowIndex ]['end_date']   = Yii::$app->formatter->asDate( $rate['reading_to_date'], 'ddMMyy' );

                    // Single Shefel
                    $generateData[ $rowIndex ]['single_shefel_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_price", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_total_pay", 0 ) );

                    // Single Geva
                    $generateData[ $rowIndex ]['single_geva_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_price", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_total_pay", 0 ) );

                    // Single Pisga
                    $generateData[ $rowIndex ]['single_pisga_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_price", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_total_pay", 0 ) );

                    // General TAOZ
                    $generateData[ $rowIndex ]['general_taoz_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_price", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_total_pay", 0 ) );

                    // Group Shefel
                    $generateData[ $rowIndex ]['group_shefel_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['group_shefel_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['group_shefel_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_diff", 0 ) + $rate['shefel']['reading_diff'] );
                    $generateData[ $rowIndex ]['group_shefel_price']        = static::getFormattedNumber( $rate['shefel']['price'] );
                    $generateData[ $rowIndex ]['group_shefel_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_total_pay", 0 ) + $rate['shefel']['total_pay'] );

                    // Group Geva
                    $generateData[ $rowIndex ]['group_geva_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['group_geva_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['group_geva_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_diff", 0 ) + $rate['geva']['reading_diff'] );
                    $generateData[ $rowIndex ]['group_geva_price']        = static::getFormattedNumber( $rate['geva']['price'] );
                    $generateData[ $rowIndex ]['group_geva_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_total_pay", 0 ) + $rate['geva']['total_pay'] );

                    // Group Pisga
                    $generateData[ $rowIndex ]['group_pisga_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['group_pisga_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['group_pisga_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_diff", 0 ) + $rate['pisga']['reading_diff'] );
                    $generateData[ $rowIndex ]['group_pisga_price']        = static::getFormattedNumber( $rate['pisga']['price'] );
                    $generateData[ $rowIndex ]['group_pisga_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_total_pay", 0 ) + $rate['pisga']['total_pay'] );

                    // General Fixed
                    $generateData[ $rowIndex ]['general_fixed_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['general_fixed_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['general_fixed_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['general_fixed_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_price", 0 ) );
                    $generateData[ $rowIndex ]['general_fixed_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_total_pay", 0 ) );

                    //Collate rule totals
                    $total_pay = $rate['shefel']['total_pay'] + $rate['geva']['total_pay'] + $rate['pisga']['total_pay'];


                    //Overwrites totals for each row
                    if ( $rowIndex == $totalIndex ) {
                      $generateData[ $rowIndex ]['fixed_payment']         = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.fixed_payment", 0 ) );
                      $generateData[ $rowIndex ]['total_pay_without_vat'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay );
                      $generateData[ $rowIndex ]['vat_total_pay']         = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage );
                      $generateData[ $rowIndex ]['total_pay_with_vat']    = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage ) );
                      if ( in_array( $power_factor_visibility, [
                        Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                        Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                      ] ) ) {
                        $generateData[ $rowIndex ]['power_factor'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.power_factor", 0 ) );
                      }
                    } else {
                      $generateData[ $rowIndex ]['fixed_payment']         = static::getFormattedNumber( 0.00 );
                      $generateData[ $rowIndex ]['total_pay_without_vat'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay );
                      $generateData[ $rowIndex ]['vat_total_pay']         = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage );
                      $generateData[ $rowIndex ]['total_pay_with_vat']    = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage ) );
                      if ( in_array( $power_factor_visibility, [
                        Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                        Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                      ] ) ) {
                        $generateData[ $rowIndex ]['power_factor'] = static::getFormattedNumber( 0.00 );
                      }
                    }
                    $total_pay = 0;
                  }
                  break;

                case RuleFixedLoad::USE_TYPE_KWH_FIXED: //5
                  foreach ( $rule['rates'] as $rateIndex => $rate ) {
                    $rowIndex = "$index.$rateIndex";

                    // Base
                    $generateData[ $rowIndex ]['tenant_id']  = $tenant_id;
                    $generateData[ $rowIndex ]['start_date'] = Yii::$app->formatter->asDate( $rate['reading_from_date'], 'ddMMyy' );
                    $generateData[ $rowIndex ]['end_date']   = Yii::$app->formatter->asDate( $rate['reading_to_date'], 'ddMMyy' );

                    // Single Shefel
                    $generateData[ $rowIndex ]['single_shefel_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_price", 0 ) );
                    $generateData[ $rowIndex ]['single_shefel_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_total_pay", 0 ) );

                    // Single Geva
                    $generateData[ $rowIndex ]['single_geva_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_price", 0 ) );
                    $generateData[ $rowIndex ]['single_geva_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_total_pay", 0 ) );

                    // Single Pisga
                    $generateData[ $rowIndex ]['single_pisga_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_price", 0 ) );
                    $generateData[ $rowIndex ]['single_pisga_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_total_pay", 0 ) );

                    // General TAOZ
                    $generateData[ $rowIndex ]['general_taoz_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_price", 0 ) );
                    $generateData[ $rowIndex ]['general_taoz_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_total_pay", 0 ) );

                    // Group Shefel
                    $generateData[ $rowIndex ]['group_shefel_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['group_shefel_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['group_shefel_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['group_shefel_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_price", 0 ) );
                    $generateData[ $rowIndex ]['group_shefel_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_total_pay", 0 ) );

                    // Group Geva
                    $generateData[ $rowIndex ]['group_geva_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['group_geva_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['group_geva_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['group_geva_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_price", 0 ) );
                    $generateData[ $rowIndex ]['group_geva_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_total_pay", 0 ) );

                    // Group Pisga
                    $generateData[ $rowIndex ]['group_pisga_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['group_pisga_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['group_pisga_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_diff", 0 ) );
                    $generateData[ $rowIndex ]['group_pisga_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_price", 0 ) );
                    $generateData[ $rowIndex ]['group_pisga_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_total_pay", 0 ) );

                    // General Fixedpa
                    $generateData[ $rowIndex ]['general_fixed_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_from", 0 ) );
                    $generateData[ $rowIndex ]['general_fixed_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_to", 0 ) );
                    $generateData[ $rowIndex ]['general_fixed_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_diff", 0 ) + $rate['fixed']['reading_diff'] );
                    $generateData[ $rowIndex ]['general_fixed_price']        = static::getFormattedNumber( $rate['price'] );
                    $generateData[ $rowIndex ]['general_fixed_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_total_pay", 0 ) + $rate['fixed']['total_pay'] );

                    //Collate rule totals
                    $total_pay = $rate['fixed']['total_pay'];


                    //Overwrites totals for each row
                    if ( $rowIndex == $totalIndex ) {
                      $generateData[ $rowIndex ]['fixed_payment']         = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.fixed_payment", 0 ) );
                      $generateData[ $rowIndex ]['total_pay_without_vat'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay );
                      $generateData[ $rowIndex ]['vat_total_pay']         = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage );
                      $generateData[ $rowIndex ]['total_pay_with_vat']    = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage ) );
                      if ( in_array( $power_factor_visibility, [
                        Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                        Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                      ] ) ) {
                        $generateData[ $rowIndex ]['power_factor'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.power_factor", 0 ) );
                      }
                    } else {
                      $generateData[ $rowIndex ]['fixed_payment']         = static::getFormattedNumber( 0.00 );
                      $generateData[ $rowIndex ]['total_pay_without_vat'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay );
                      $generateData[ $rowIndex ]['vat_total_pay']         = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage );
                      $generateData[ $rowIndex ]['total_pay_with_vat']    = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage ) );
                      if ( in_array( $power_factor_visibility, [
                        Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                        Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                      ] ) ) {
                        $generateData[ $rowIndex ]['power_factor'] = static::getFormattedNumber( 0.00 );
                      }
                    }
                    $total_pay = 0;
                    break;
                  }
                  break;

                case RuleFixedLoad::USE_TYPE_MONEY:  // 1
                case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE:  // 4
                case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT: // 3

                default:
                  switch ( $row['rate_type'] ) {
                    case RateType::TYPE_TAOZ:
                      // Base
                      $generateData[ $rowIndex ]['tenant_id']  = $tenant_id;
                      $generateData[ $rowIndex ]['start_date'] = ArrayHelper::getValue( $generateData, "$rowIndex.start_date", null );
                      $generateData[ $rowIndex ]['end_date']   = ArrayHelper::getValue( $generateData, "$rowIndex.end_date", null );

                      // Single Shefel
                      $generateData[ $rowIndex ]['single_shefel_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['single_shefel_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['single_shefel_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['single_shefel_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_price", 0 ) );
                      $generateData[ $rowIndex ]['single_shefel_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_total_pay", 0 ) );

                      // Single Geva
                      $generateData[ $rowIndex ]['single_geva_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['single_geva_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['single_geva_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['single_geva_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_price", 0 ) );
                      $generateData[ $rowIndex ]['single_geva_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_total_pay", 0 ) );

                      // Single Pisga
                      $generateData[ $rowIndex ]['single_pisga_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['single_pisga_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['single_pisga_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['single_pisga_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_price", 0 ) );
                      $generateData[ $rowIndex ]['single_pisga_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_total_pay", 0 ) );

                      // General TAOZ
                      $generateData[ $rowIndex ]['general_taoz_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['general_taoz_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['general_taoz_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['general_taoz_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_price", 0 ) );
                      $generateData[ $rowIndex ]['general_taoz_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_total_pay", 0 ) );

                      // Group Shefel
                      $generateData[ $rowIndex ]['group_shefel_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['group_shefel_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['group_shefel_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['group_shefel_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_price", 0 ) );
                      $generateData[ $rowIndex ]['group_shefel_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_total_pay", 0 ) + $rule['total_pay'] / 3 );

                      // Group Geva
                      $generateData[ $rowIndex ]['group_geva_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['group_geva_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['group_geva_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['group_geva_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_price", 0 ) );
                      $generateData[ $rowIndex ]['group_geva_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_total_pay", 0 ) + $rule['total_pay'] / 3 );

                      // Group Pisga
                      $generateData[ $rowIndex ]['group_pisga_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['group_pisga_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['group_pisga_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['group_pisga_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_price", 0 ) );
                      $generateData[ $rowIndex ]['group_pisga_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_total_pay", 0 ) + $rule['total_pay'] / 3 );

                      // General Fixed
                      $generateData[ $rowIndex ]['general_fixed_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['general_fixed_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['general_fixed_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['general_fixed_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_price", 0 ) );
                      $generateData[ $rowIndex ]['general_fixed_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_total_pay", 0 ) );

                      //Collate rule totals
                      $total_pay = $rule['total_pay'];


                      //Overwrites totals for each row
                      if ( $rowIndex == $totalIndex ) {
                        $generateData[ $rowIndex ]['fixed_payment']         = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.fixed_payment", 0 ) );
                        $generateData[ $rowIndex ]['total_pay_without_vat'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay );
                        $generateData[ $rowIndex ]['vat_total_pay']         = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage );
                        $generateData[ $rowIndex ]['total_pay_with_vat']    = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage ) );
                        if ( in_array( $power_factor_visibility, [
                          Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                          Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                        ] ) ) {
                          $generateData[ $rowIndex ]['power_factor'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.power_factor", 0 ) );
                        }
                      } else {
                        $generateData[ $rowIndex ]['fixed_payment']         = static::getFormattedNumber( 0.00 );
                        $generateData[ $rowIndex ]['total_pay_without_vat'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay );
                        $generateData[ $rowIndex ]['vat_total_pay']         = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage );
                        $generateData[ $rowIndex ]['total_pay_with_vat']    = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage ) );
                        if ( in_array( $power_factor_visibility, [
                          Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                          Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                        ] ) ) {
                          $generateData[ $rowIndex ]['power_factor'] = static::getFormattedNumber( 0.00 );
                        }
                      }
                      $total_pay = 0;

                      break;

                    default:
                      // Base
                      $generateData[ $rowIndex ]['tenant_id']  = $tenant_id;
                      $generateData[ $rowIndex ]['start_date'] = ArrayHelper::getValue( $generateData, "$rowIndex.start_date", null );
                      $generateData[ $rowIndex ]['end_date']   = ArrayHelper::getValue( $generateData, "$rowIndex.end_date", null );

                      // Single Shefel
                      $generateData[ $rowIndex ]['single_shefel_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['single_shefel_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['single_shefel_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['single_shefel_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_price", 0 ) );
                      $generateData[ $rowIndex ]['single_shefel_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_shefel_total_pay", 0 ) );

                      // Single Geva
                      $generateData[ $rowIndex ]['single_geva_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['single_geva_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['single_geva_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['single_geva_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_price", 0 ) );
                      $generateData[ $rowIndex ]['single_geva_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_geva_total_pay", 0 ) );

                      // Single Pisga
                      $generateData[ $rowIndex ]['single_pisga_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['single_pisga_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['single_pisga_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['single_pisga_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_price", 0 ) );
                      $generateData[ $rowIndex ]['single_pisga_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.single_pisga_total_pay", 0 ) );

                      // General TAOZ
                      $generateData[ $rowIndex ]['general_taoz_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['general_taoz_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['general_taoz_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['general_taoz_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_price", 0 ) );
                      $generateData[ $rowIndex ]['general_taoz_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_taoz_total_pay", 0 ) );

                      // Group Shefel
                      $generateData[ $rowIndex ]['group_shefel_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['group_shefel_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['group_shefel_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['group_shefel_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_price", 0 ) );
                      $generateData[ $rowIndex ]['group_shefel_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_shefel_total_pay", 0 ) );

                      // Group Geva
                      $generateData[ $rowIndex ]['group_geva_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['group_geva_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['group_geva_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['group_geva_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_price", 0 ) );
                      $generateData[ $rowIndex ]['group_geva_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_geva_total_pay", 0 ) );

                      // Group Pisga
                      $generateData[ $rowIndex ]['group_pisga_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['group_pisga_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['group_pisga_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['group_pisga_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_price", 0 ) );
                      $generateData[ $rowIndex ]['group_pisga_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.group_pisga_total_pay", 0 ) );

                      // General Fixed
                      $generateData[ $rowIndex ]['general_fixed_reading_from'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_from", 0 ) );
                      $generateData[ $rowIndex ]['general_fixed_reading_to']   = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_to", 0 ) );
                      $generateData[ $rowIndex ]['general_fixed_reading_diff'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_reading_diff", 0 ) );
                      $generateData[ $rowIndex ]['general_fixed_price']        = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.general_fixed_price", 0 ) );
                      $generateData[ $rowIndex ]['general_fixed_total_pay']    = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$totalIndex.general_fixed_total_pay", 0 ) + $rule['total_pay'] );

                      //Collate rule totals
                      $total_pay = $rule['total_pay'];


                      //Overwrites totals for each row
                      if ( $rowIndex == $totalIndex ) {
                        $generateData[ $rowIndex ]['fixed_payment']         = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.fixed_payment", 0 ) );
                        $generateData[ $rowIndex ]['total_pay_without_vat'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay );
                        $generateData[ $rowIndex ]['vat_total_pay']         = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage );
                        $generateData[ $rowIndex ]['total_pay_with_vat']    = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage ) );
                        if ( in_array( $power_factor_visibility, [
                          Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                          Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                        ] ) ) {
                          $generateData[ $rowIndex ]['power_factor'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.power_factor", 0 ) );
                        }
                      } else {
                        $generateData[ $rowIndex ]['fixed_payment']         = static::getFormattedNumber( 0.00 );
                        $generateData[ $rowIndex ]['total_pay_without_vat'] = static::getFormattedNumber( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay );
                        $generateData[ $rowIndex ]['vat_total_pay']         = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * $vat_percentage );
                        $generateData[ $rowIndex ]['total_pay_with_vat']    = static::getFormattedNumber( ( ArrayHelper::getValue( $generateData, "$rowIndex.total_pay_without_vat", 0 ) + $total_pay ) * ( 1 + $vat_percentage ) );
                        if ( in_array( $power_factor_visibility, [
                          Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                          Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS
                        ] ) ) {
                          $generateData[ $rowIndex ]['power_factor'] = static::getFormattedNumber( 0.00 );
                        }
                      }
                      $total_pay = 0;
                      break;
                  }
                  break;
              }
              break;


            /**
             * Default case
             */
            default:
              break;

          }
        }
        $total_pay = 0;
        $index ++;

        //****************/MAIN**********************

      }
    }

    return static::getNormalizedData( $generateData );
  }

  /**
   * Returns normalized data
   * @return array
   */
  public static function getNormalizedData( $values ) {
    $length = [];

    $values = array_values( $values );

    if ( $values != null ) {
      foreach ( $values as $key => &$value ) {
        foreach ( $value as $id => &$name ) {
          switch ( $id ) {
            case 'tenant_id':
              $length[ $id ] = max( [ strlen( $name ), ArrayHelper::getValue( $length, $id, 5 ) ] );
              break;
            case 'start_date':
              $length[ $id ] = max( [ strlen( $name ), ArrayHelper::getValue( $length, $id, 6 ) ] );
              break;
            case 'end_date':
              $length[ $id ] = max( [ strlen( $name ), ArrayHelper::getValue( $length, $id, 6 ) ] );
              break;
            default:
              $name          = is_numeric( $name ) ? self::getFormattedNumber( $name ) : $name;
              $length[ $id ] = max( [ strlen( $name ), ArrayHelper::getValue( $length, $id, 10 ) ] );
              break;
          }
        }
      }
    }

    return [ 'length' => $length, 'values' => $values ];
  }

  /**
   * Returns the formatted value
   *
   * @param integer|double $value
   *
   * @return string
   */
  public static function getFormattedNumber( $value ) {
    return str_pad( number_format( $value, 2, '.', '' ), 13, '0', STR_PAD_LEFT );
  }
}