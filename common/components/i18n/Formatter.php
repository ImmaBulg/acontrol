<?php
namespace common\components\i18n;

use DateInterval;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use IntlDateFormatter;
use NumberFormatter;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

use yii\db\Query;
use common\models\Vat;

/**
 * Formatter provides a set of commonly used data formatting methods.
 *
 * The formatting methods provided by Formatter are all named in the form of `asXyz()`.
 * The behavior of some of them may be configured via the properties of Formatter. For example,
 * by configuring [[dateFormat]], one may control how [[asDate()]] formats the value into a date string.
 *
 * Formatter is configured as an application component in [[\yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->formatter`.
 *
 * The Formatter class is designed to format values according to a [[locale]]. For this feature to work
 * the [PHP intl extension](http://php.net/manual/en/book.intl.php) has to be installed.
 * Most of the methods however work also if the PHP intl extension is not installed by providing
 * a fallback implementation. Without intl month and day names are in English only.
 * Note that even if the intl extension is installed, formatting date and time values for years >=2038 or <=1901
 * on 32bit systems will fall back to the PHP implementation because intl uses a 32bit UNIX timestamp internally.
 * On a 64bit system the intl formatter is used in all cases if installed.
 *
 * ICU - http://userguide.icu-project.org/formatparse/datetime
 */
class Formatter extends \yii\i18n\Formatter
{
    public $dateFormat = 'dd-MM-yyyy';
	public $timeFormat = 'HH:mm';
	public $datetimeFormat = 'dd-MM-yyyy HH:mm';

	const JS_WEEK_START = 1;
	const JS_DATE_FORMAT = 'd-m-Y';
	const SITE_DATE_FORMAT = 'd-m-Y';
	const STORAGE_DATE_TIME_FORMAT = 'Y-m-d H:i:s';
	const PHP_DATE_FORMAT = 'php:d-m-Y';
    const PHP_DATE_TIME_FORMAT = 'php:d-m-Y H:i';
    const PHP_DATE_TIME_FORMAT_ZERO_MINUTE = 'php:d-m-Y H:0';
    const PHP_TIME_FORMAT = 'php:H:i';
	const SQL_DATE_FORMAT = '%d-%m-%Y';
	const SQL_TIME_FORMAT = '%h:%i';
	const SQL_DATE_TIME_FORMAT = '%d-%m-%Y %H:%i';
	
	const HUMAN_TIME_RANGE = 86400; // 1 day

	private function formatHumanDateTime($value)
	{
		if ((time() - $value) < self::HUMAN_TIME_RANGE) {
			return $this->asRelativeTime($value);
		}
	}

	public function asHumanDate($value)
	{
		if ($value != null) {
			$formatDateTime = $this->formatHumanDateTime($value);
			return ($formatDateTime) ? $formatDateTime : $this->asDate($value);
		}
	}

	public function asHumanDateTime($value)
	{
		if ($value != null) {
			$formatDateTime = $this->formatHumanDateTime($value);
			return ($formatDateTime) ? $formatDateTime : $this->asDateTime($value);
		}
	}

    public function asRound($value, $precision = 2, $dec_point = '.', $thousands_sep = '')
    {
        if (!is_null($value)) {
            return number_format($value, $precision, $dec_point, $thousands_sep);
        } else {
            return null;
        }
    }

	public function asNumberFormat($value, $precision = 2)
	{
		if (!is_null($value)) {
			if (strpos($value, 'E-') !== false) {
				return $value;
			} else {
				if (strpos($value, '0.') !== false) {
					$decimals = rtrim(str_replace('0.', '', fmod(abs($value), 1)), '0');
					$decimals_precision = 1;

					for ($i = 0; $i < strlen($decimals); $i++) {
						if ($decimals[$i]) {
							break;
						}

						$decimals_precision++;
					}

					$precision = ($precision > $decimals_precision) ? $precision : $decimals_precision;
				}

				return number_format($value, $precision, '.', ',');
			}
		} else {
			return null;
		}
	}

	public function asPercentage($value, $precision = 2)
	{
		if (!is_null($value)) {
			return number_format($value, $precision, '.', ''). "%";
		} else {
			return null;
		}
	}

	public function asPrice($value, $precision = 2)
	{
		return number_format($value, $precision, '.', ','). " " .Yii::t('common.common', 'NIS');
	}

	public function normalizeDatetimeValue($value, $checkTimeInfo = false)
	{
		// checking for DateTime and DateTimeInterface is not redundant, DateTimeInterface is only in PHP>5.5
		if ($value === null || $value instanceof DateTime || $value instanceof DateTimeInterface) {
			// skip any processing
			return $checkTimeInfo ? [$value, true] : $value;
		}
		if (empty($value)) {
			$value = 0;
		}
		try {
			if (is_numeric($value)) { // process as unix timestamp, which is always in UTC
				if (($timestamp = DateTime::createFromFormat('U', $value, new DateTimeZone('UTC'))) === false) {
					throw new InvalidParamException("Failed to parse '$value' as a UNIX timestamp.");
				}
				return $checkTimeInfo ? [$timestamp, true] : $timestamp;
			} elseif (($timestamp = DateTime::createFromFormat('Y-m-d', $value, new DateTimeZone($this->defaultTimeZone))) !== false) { // try Y-m-d format (support invalid dates like 2012-13-01)
				return $checkTimeInfo ? [$timestamp, false] : $timestamp;
			} elseif (($timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $value, new DateTimeZone($this->defaultTimeZone))) !== false) { // try Y-m-d H:i:s format (support invalid dates like 2012-13-01 12:63:12)
				return $checkTimeInfo ? [$timestamp, true] : $timestamp;
			}
			// finally try to create a DateTime object with the value
			if ($checkTimeInfo) {
				$timestamp = new DateTime($value, new DateTimeZone($this->defaultTimeZone));
				$info = date_parse($value);
				return [$timestamp, !($info['hour'] === false && $info['minute'] === false && $info['second'] === false)];
			} else {
				return new DateTime($value, new DateTimeZone($this->defaultTimeZone));
			}
		} catch(\Exception $e) {
			$value = false;
		}
	}


    /**
     * Modify timestamp value.
     * @param integer|string|DateTime $value the datetime value to be normalized.
     * @param string $modify parameter to change timestamp
     * @return null|string
     */
	public function modifyTimestamp($value, $modify)
	{
		$dateTime = $this->normalizeDatetimeValue($value);

		if ($dateTime != null) {
			$dateTime->modify($modify);
			return number_format($dateTime->format('U'), 0, '.', '');
		} else {
			return null;
		}
	}


    /**
     * Getting VAT by period
     * @param integer|string|DateTime $from_date
     * @param integer|string|DateTime $end_date
     * @return int
     */
	public function getVat($from_date = null, $end_date = null)
	{
		$from_date = (!is_null($from_date)) ? $this->asTimestamp($from_date) : strtotime('midnight');
		$end_date = (!is_null($end_date)) ? $this->asTimestamp($end_date) : strtotime('tomorrow') - 1;
		$vat = (new Query())->select('vat')->from(Vat::tableName())
		->andWhere('start_date <= :from_date AND (end_date IS NULL OR end_date >= :end_date)', [
			'from_date' => $from_date,
			'end_date' => $end_date,
		])->orderBy(['id' => SORT_DESC])->one();
		return (!empty($vat)) ? $vat['vat'] : 0;
	}
}