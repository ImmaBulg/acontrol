<?php

namespace common\helpers;

use \DateTime;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use common\models\Site;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterChannelMultiplier;

class MetmonRealTime
{
	const STATUS_INITIAL = 0;
	const STATUS_UNKNOWN = 1;
	const STATUS_OK = 2;
	const STATUS_TIMEOUT = 3;
	const STATUS_ERROR = 4;
	const STATUS_PENDING = 5;

	/**
	 * Generate
	 * 
	 * @param \common\models\Site $site
	 * @param \common\models\Meter $meter
	 * @param \common\models\MeterChannel $channel
	 * @return array
	 */
	public static function generate(Site $site, Meter $meter, MeterChannel $channel)
	{
		$data = [];
		$ip = $meter->getIpAddress();
		$port = '10202';
		$meter_id = $meter->name;
		$meter_type = $meter->relationMeterType;
		$meter_type_name = strtolower($meter_type->name);
		$phases = $meter_type->phases;
		$auth = '';

		if ($phases > 1) {
			$channel_id = implode(',', ArrayHelper::map($channel->relationMeterSubchannels, 'id', 'channel'));
			$criteria_meter_types = ['qng3', 'mc4', 'mc5', 'rsm4', 'rsm5'];

			foreach ($criteria_meter_types as $criteria_meter_type) {
				if (strpos($meter_type_name, $criteria_meter_type) !== false) {
					$channel_id = $channel->channel;
					break;
				}
			}
		} else {
			$channel_id = $channel->channel;
		}

		$url = "http://$ip:$port/rt?" . urldecode(http_build_query([
			'meter_id' => $meter_id,
			'channel' => $channel_id,
			'auth' => $auth,
		], '', '&', PHP_QUERY_RFC3986));

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);
		$response = curl_exec($curl);
		$headers = curl_getinfo($curl);
		curl_close($curl);

		if (strncmp($headers['http_code'], '20', 2) === 0 && $response != null) {
			$xml = (new \SimpleXMLElement($response));

			if (!empty($xml->channel)) {
				$VvLength = 0;
				$IvLength = 0;
				$PFLength = 0;

				$data = [
					/**
					 * Va
					 * Vb
					 * Vc
					 * Vv
					 */
					'Va' => [
						'name' => 'Va',
						'value' => 0,
						'date' => NULL,
					],
					'Vb' => [
						'name' => 'Vb',
						'value' => 0,
						'date' => NULL,
					],
					'Vc' => [
						'name' => 'Vc',
						'value' => 0,
						'date' => NULL,
					],
					'Vv' => [
						'name' => 'Vv',
						'value' => 0,
						'date' => NULL,
					],
					/**
					 * Ia
					 * Ib
					 * Ic
					 * Iv
					 */
					'Ia' => [
						'name' => 'Ia',
						'value' => 0,
						'date' => NULL,
					],
					'Ib' => [
						'name' => 'Ib',
						'value' => 0,
						'date' => NULL,
					],
					'Ic' => [
						'name' => 'Ic',
						'value' => 0,
						'date' => NULL,
					],
					'Iv' => [
						'name' => 'Iv',
						'value' => 0,
						'date' => NULL,
					],
					'IvLimit' => [
						'name' => 'IvLimit',
						'value' => (strpos($meter_type_name, 'qng3') !== false) ? 3000 : $channel->current_multiplier * 100,
					],
					/**
					 * KWa
					 * KWb
					 * KWc
					 * KW
					 */
					'KWa' => [
						'name' => 'KWa',
						'value' => 0,
						'date' => NULL,
					],
					'KWb' => [
						'name' => 'KWb',
						'value' => 0,
						'date' => NULL,
					],
					'KWc' => [
						'name' => 'KWc',
						'value' => 0,
						'date' => NULL,
					],
					'KW' => [
						'name' => 'KW',
						'value' => 0,
						'date' => NULL,
					],
					/**
					 * PFa
					 * PFb
					 * PFc
					 * PF
					 */
					'PFa' => [
						'name' => 'PFa',
						'value' => 0,
						'date' => NULL,
					],
					'PFb' => [
						'name' => 'PFb',
						'value' => 0,
						'date' => NULL,
					],
					'PFc' => [
						'name' => 'PFc',
						'value' => 0,
						'date' => NULL,
					],
					'PF' => [
						'name' => 'PF',
						'value' => 0,
						'date' => NULL,
					],
					/**
					 * Tf1TotImpKWh
					 * Tf2TotImpKWh
					 * Tf3TotImpKWh
					 * TfTotImpKWh
					 */
					'Tf1TotImpKWh' => [
						'name' => 'Tf1TotImpKWh',
						'value' => 0,
						'date' => NULL,
					],
					'Tf2TotImpKWh' => [
						'name' => 'Tf2TotImpKWh',
						'value' => 0,
						'date' => NULL,
					],
					'Tf3TotImpKWh' => [
						'name' => 'Tf3TotImpKWh',
						'value' => 0,
						'date' => NULL,
					],
					'TfTotImpKWh' => [
						'name' => 'TfTotImpKWh',
						'value' => 0,
						'date' => NULL,
					],
					/**
					 * Tf1TotExpKWh
					 * Tf2TotExpKWh
					 * Tf3TotExpKWh
					 * TfTotExpKWh
					 */
					'Tf1TotExpKWh' => [
						'name' => 'Tf1TotExpKWh',
						'value' => 0,
						'date' => NULL,
					],
					'Tf2TotExpKWh' => [
						'name' => 'Tf2TotExpKWh',
						'value' => 0,
						'date' => NULL,
					],
					'Tf3TotExpKWh' => [
						'name' => 'Tf3TotExpKWh',
						'value' => 0,
						'date' => NULL,
					],
					'TfTotExpKWh' => [
						'name' => 'TfTotExpKWh',
						'value' => 0,
						'date' => NULL,
					],
				];
				$xmlChannelIndex = 0;
				$alphabetRange = range('a', 'z');

				if (strpos($meter_type_name, 'qng3') !== false) {
					$multiplierIa = 1;
					$multiplierKW = 1;
					$multiplierTotImpKWh = 1;
					$multiplierTotExpKWh = 1;
				} elseif (strpos($meter_type_name, 'qng16') !== false) {
					$multiplierIa = 10;

					if ($channel->channel == 15) {
						$multiplierKW = 10;
					} else {
						$multiplierKW = 1;
					}

					$multiplierTotImpKWh = 100;
					$multiplierTotExpKWh = 100;
				} else {
					$multiplierIa = 1;
					$multiplierKW = 1;
					$multiplierTotImpKWh = 1;
					$multiplierTotExpKWh = 1;
				}

				foreach ($xml->channel as $xmlChannel) {
					$xmlChannelIndex++;
					$xmlChannelId = (string) $xmlChannel->attributes()->id;
					$xmlChannelPhases = (int) $xmlChannel->attributes()->phases;

					if (!empty($xmlChannel->tags)) {
						/**
						 * Phase 3
						 */
						if ($xmlChannelPhases > 1) {
							foreach ($xmlChannel->tags[0] as $xmlChannelTag) {
								$xmlChannelTagName = (string) $xmlChannelTag->attributes()->name;
								$xmlChannelTagStatus = (int) $xmlChannelTag->status;
								$xmlChannelTagValue = (float) $xmlChannelTag->value;
								$xmlChannelTagDate = (string) $xmlChannelTag->dt;
								
								if ($xmlChannelTagStatus == static::STATUS_OK) {
									$date = [
										'y' => Yii::$app->formatter->asDate($xmlChannelTagDate, 'yyyy'),
										'd' => Yii::$app->formatter->asDate($xmlChannelTagDate, 'd'),
										'm' => Yii::$app->formatter->asDate($xmlChannelTagDate, 'M'),
										'h' => Yii::$app->formatter->asDate($xmlChannelTagDate, 'H'),
										'i' => Yii::$app->formatter->asDate($xmlChannelTagDate, 'm'),
										's' => Yii::$app->formatter->asDate($xmlChannelTagDate, 's'),
										't' => Yii::$app->formatter->asTimestamp($xmlChannelTagDate),
									];

									switch ($xmlChannelTagName) {
										/**
										 * Va
										 * Vb
										 * Vc
										 * Vv
										 */
										case "Va":
										case "Vb":
										case "Vc":
											$data[$xmlChannelTagName]['value'] = $xmlChannelTagValue * $channel->voltage_multiplier;
											$data[$xmlChannelTagName]['date'] = $date;
											$data['Vv']['date'] = $date;
											$VvLength++;
											break;

										/**
										 * Ia
										 * Ib
										 * Ic
										 * Iv
										 */
										case "Ia{$xmlChannelId}":
											$data['Ia']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Ia']['date'] = $date;
											$data['Iv']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Iv']['date'] = $date;
											$IvLength++;
											break;

										case "Ib{$xmlChannelId}":
											$data['Ib']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Ib']['date'] = $date;
											$data['Iv']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Iv']['date'] = $date;
											$IvLength++;
											break;

										case "Ic{$xmlChannelId}":
											$data['Ic']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Ic']['date'] = $date;
											$data['Iv']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Iv']['date'] = $date;
											$IvLength++;
											break;

										case "qng3C":
											if ((strpos($meter_type_name, 'qng3') !== false) && $xmlChannelTagValue > 0) {
												$data['IvLimit']['value'] = 5 * $xmlChannelTagValue;
												$multiplierIa = $xmlChannelTagValue;
												$multiplierKW = $xmlChannelTagValue / 10;
											}
											break;

										/**
										 * KWa
										 * KWb
										 * KWc
										 * KW
										 */
										case "KWa{$xmlChannelId}":
											if (strpos($meter_type_name, 'qng3') !== false) {
												$data['KWa']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier) / 1000;
												$data['KWa']['date'] = $date;
												$data['KW']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier) / 1000;
												$data['KW']['date'] = $date;
											} else {
												$data['KWa']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier);
												$data['KWa']['date'] = $date;
												$data['KW']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier);
												$data['KW']['date'] = $date;
											}
											break;

										case "KWb{$xmlChannelId}":
											if (strpos($meter_type_name, 'qng3') !== false) {
												$data['KWb']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier) / 1000;
												$data['KWb']['date'] = $date;
												$data['KW']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier) / 1000;
												$data['KW']['date'] = $date;
											} else {
												$data['KWb']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier);
												$data['KWb']['date'] = $date;
												$data['KW']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier);
												$data['KW']['date'] = $date;
											}
											break;

										case "KWc{$xmlChannelId}":
											if (strpos($meter_type_name, 'qng3') !== false) {
												$data['KWc']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier) / 1000;
												$data['KWc']['date'] = $date;
												$data['KW']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier) / 1000;
												$data['KW']['date'] = $date;
											} else {
												$data['KWc']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier);
												$data['KWc']['date'] = $date;
												$data['KW']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier);
												$data['KW']['date'] = $date;
											}
											break;

										/**
										 * PFa
										 * PFb
										 * PFc
										 * PF
										 */
										case "PFa{$xmlChannelId}":
											$data['PFa']['value'] += $xmlChannelTagValue;
											$data['PFa']['date'] = $date;
											$data['PF']['value'] += $xmlChannelTagValue;
											$data['PF']['date'] = $date;
											$PFLength++;
											break;

										case "PFb{$xmlChannelId}":
											$data['PFb']['value'] += $xmlChannelTagValue;
											$data['PFb']['date'] = $date;
											$data['PF']['value'] += $xmlChannelTagValue;
											$data['PF']['date'] = $date;
											$PFLength++;
											break;

										case "PFc{$xmlChannelId}":
											$data['PFc']['value'] += $xmlChannelTagValue;
											$data['PFc']['date'] = $date;
											$data['PF']['value'] += $xmlChannelTagValue;
											$data['PF']['date'] = $date;
											$PFLength++;
											break;

										/**
										 * Tf1TotImpKWh
										 * Tf2TotImpKWh
										 * Tf3TotImpKWh
										 * TfTotImpKWh
										 */
										case "Tf1TotImpKWh{$xmlChannelId}":
											$data['Tf1TotImpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Tf1TotImpKWh']['date'] = $date;
											$data['TfTotImpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['TfTotImpKWh']['date'] = $date;
											break;

										case "Tf2TotImpKWh{$xmlChannelId}":
											$data['Tf2TotImpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Tf2TotImpKWh']['date'] = $date;
											$data['TfTotImpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['TfTotImpKWh']['date'] = $date;
											break;

										case "Tf3TotImpKWh{$xmlChannelId}":
											$data['Tf3TotImpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Tf3TotImpKWh']['date'] = $date;
											$data['TfTotImpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['TfTotImpKWh']['date'] = $date;
											break;

										/**
										 * Tf1TotExpKWh
										 * Tf2TotExpKWh
										 * Tf3TotExpKWh
										 * TfTotExpKWh
										 */
										case "Tf1TotExpKWh{$xmlChannelId}":
											$data['Tf1TotExpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Tf1TotExpKWh']['date'] = $date;
											$data['TfTotExpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['TfTotExpKWh']['date'] = $date;
											break;

										case "Tf2TotExpKWh{$xmlChannelId}":
											$data['Tf2TotExpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Tf2TotExpKWh']['date'] = $date;
											$data['TfTotExpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['TfTotExpKWh']['date'] = $date;
											break;

										case "Tf3TotExpKWh{$xmlChannelId}":
											$data['Tf3TotExpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Tf3TotExpKWh']['date'] = $date;
											$data['TfTotExpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['TfTotExpKWh']['date'] = $date;
											break;

										default:
											break;
									}
								}
							}
						}
						/**
						 * Phase 1
						 */
						else {
							foreach ($xmlChannel->tags[0] as $xmlChannelTag) {
								$xmlChannelTagName = (string) $xmlChannelTag->attributes()->name;
								$xmlChannelTagStatus = (int) $xmlChannelTag->status;
								$xmlChannelTagValue = (float) $xmlChannelTag->value;
								$xmlChannelTagDate = (string) $xmlChannelTag->dt;
								
								if ($xmlChannelTagStatus == static::STATUS_OK) {
									$date = [
										'y' => Yii::$app->formatter->asDate($xmlChannelTagDate, 'yyyy'),
										'd' => Yii::$app->formatter->asDate($xmlChannelTagDate, 'd'),
										'm' => Yii::$app->formatter->asDate($xmlChannelTagDate, 'M'),
										'h' => Yii::$app->formatter->asDate($xmlChannelTagDate, 'H'),
										'i' => Yii::$app->formatter->asDate($xmlChannelTagDate, 'm'),
										's' => Yii::$app->formatter->asDate($xmlChannelTagDate, 's'),
										't' => Yii::$app->formatter->asTimestamp($xmlChannelTagDate),
									];

									switch ($xmlChannelTagName) {
										/**
										 * Va
										 * Vb
										 * Vc
										 * Vv
										 */
										case "Va":
										case "Vb":
										case "Vc":
											$data[$xmlChannelTagName]['value'] = $xmlChannelTagValue * $channel->voltage_multiplier;
											$data[$xmlChannelTagName]['date'] = $date;
											$data['Vv']['date'] = $date;
											$VvLength++;
											break;

										/**
										 * Ia
										 * Ib
										 * Ic
										 * Iv
										 */
										case "I{$xmlChannelId}":
											$alphabetIndex = $alphabetRange[$xmlChannelIndex - 1];
											$data["I{$alphabetIndex}"]['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data["I{$alphabetIndex}"]['date'] = $date;
											$data['Iv']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Iv']['date'] = $date;
											$IvLength++;
											break;

										case "qng3C":
											if ((strpos($meter_type_name, 'qng3') !== false) && $xmlChannelTagValue > 0) {
												$data['IvLimit']['value'] = 5 * $xmlChannelTagValue;
												$multiplierIa = $xmlChannelTagValue;
												$multiplierKW = $xmlChannelTagValue / 10;
											}
											break;

										/**
										 * KWa
										 * KWb
										 * KWc
										 * KW
										 */
										case "KW{$xmlChannelId}":
											$alphabetIndex = $alphabetRange[$xmlChannelIndex - 1];

											if (strpos($meter_type_name, 'qng3') !== false) {
												$data["KW{$alphabetIndex}"]['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier) / 1000;
												$data["KW{$alphabetIndex}"]['date'] = $date;
												$data['KW']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier) / 1000;
												$data['KW']['date'] = $date;
											} else {
												$data["KW{$alphabetIndex}"]['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier);
												$data["KW{$alphabetIndex}"]['date'] = $date;
												$data['KW']['value'] += ($xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier);
												$data['KW']['date'] = $date;
											}
											break;

										/**
										 * PFa
										 * PFb
										 * PFc
										 * PF
										 */
										case "PF{$xmlChannelId}":
											$alphabetIndex = $alphabetRange[$xmlChannelIndex - 1];
											$data["PF{$alphabetIndex}"]['value'] += $xmlChannelTagValue;
											$data["PF{$alphabetIndex}"]['date'] = $date;
											$data['PF']['value'] += $xmlChannelTagValue;
											$data['PF']['date'] = $date;
											$PFLength++;
											break;

										/**
										 * Tf1TotImpKWh
										 * Tf2TotImpKWh
										 * Tf3TotImpKWh
										 * TfTotImpKWh
										 */
										case "Tf1TotImpKWh{$xmlChannelId}":
											$data['Tf1TotImpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Tf1TotImpKWh']['date'] = $date;
											$data['TfTotImpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['TfTotImpKWh']['date'] = $date;
											break;

										case "Tf2TotImpKWh{$xmlChannelId}":
											$data['Tf2TotImpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Tf2TotImpKWh']['date'] = $date;
											$data['TfTotImpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['TfTotImpKWh']['date'] = $date;
											break;

										case "Tf3TotImpKWh{$xmlChannelId}":
											$data['Tf3TotImpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Tf3TotImpKWh']['date'] = $date;
											$data['TfTotImpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['TfTotImpKWh']['date'] = $date;
											break;

										/**
										 * Tf1TotExpKWh
										 * Tf2TotExpKWh
										 * Tf3TotExpKWh
										 * TfTotExpKWh
										 */
										case "Tf1TotExpKWh{$xmlChannelId}":
											$data['Tf1TotExpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Tf1TotExpKWh']['date'] = $date;
											$data['TfTotExpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['TfTotExpKWh']['date'] = $date;
											break;

										case "Tf2TotExpKWh{$xmlChannelId}":
											$data['Tf2TotExpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Tf2TotExpKWh']['date'] = $date;
											$data['TfTotExpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['TfTotExpKWh']['date'] = $date;
											break;

										case "Tf3TotExpKWh{$xmlChannelId}":
											$data['Tf3TotExpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['Tf3TotExpKWh']['date'] = $date;
											$data['TfTotExpKWh']['value'] += $xmlChannelTagValue * $channel->current_multiplier * $channel->voltage_multiplier;
											$data['TfTotExpKWh']['date'] = $date;
											break;

										default:
											break;
									}
								}
							}
						}
					}
				}

				foreach ($data as &$item) {
					switch ($item['name']) {
						/**
						 * Ia
						 * Ib
						 * Ic
						 * Iv
						 */
						case 'Ia':
						case 'Ib':
						case 'Ic':
						case 'Iv':
							$item['value'] = $item['value'] * $multiplierIa;
							break;
						
						/**
						 * KWa
						 * KWb
						 * KWc
						 * KW
						 */
						case 'KWa':
						case 'KWb':
						case 'KWc':
						case 'KW':
							$item['value'] = $item['value'] * $multiplierKW;
							break;

						/**
						 * Tf1TotImpKWh
						 * Tf2TotImpKWh
						 * Tf3TotImpKWh
						 * TfTotImpKWh
						 */
						case 'Tf1TotImpKWh':
						case 'Tf2TotImpKWh':
						case 'Tf3TotImpKWh':
						case 'TfTotImpKWh':
							$item['value'] = $item['value'] * $multiplierTotImpKWh;
							break;

						/**
						 * Tf1TotExpKWh
						 * Tf2TotExpKWh
						 * Tf3TotExpKWh
						 * TfTotExpKWh
						 */
						case 'Tf1TotExpKWh':
						case 'Tf2TotExpKWh':
						case 'Tf3TotExpKWh':
						case 'TfTotExpKWh':
							$item['value'] = $item['value'] * $multiplierTotExpKWh;
							break;

						default:
							break;
					}
				}
				
				if ($VvLength > 0) {
					$data['Vv']['value'] = round(($data['Va']['value'] + $data['Vb']['value'] + $data['Vc']['value'])  / 3, 3);
				}

				if ($IvLength > 0) {
					$data['Iv']['value'] = round($data['Iv']['value']  / $IvLength, 3);
				}

				if ($PFLength > 0) {
					$data['PF']['value'] = round($data['PF']['value']  / $PFLength, 3);
				}
			}
		}

		return $data;
	}
}
