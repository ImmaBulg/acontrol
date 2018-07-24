<?php
namespace common\components\validators;

use Yii;
use yii\base\Exception;
use yii\validators\Validator;
use DateTime;
use IntlDateFormatter;
use yii\helpers\FormatConverter;

/**
 * Class DateTimeCompareValidator
 */
class DateTimeCompareValidator extends Validator
{
	/**
	 * @var string the date format that the value being validated should follow.
	 * This can be a date time pattern as described in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax).
	 *
	 * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the PHP Datetime class.
	 * Please refer to <http://php.net/manual/en/datetime.createfromformat.php> on supported formats.
	 *
	 * If this property is not set, the default value will be obtained from `Yii::$app->formatter->dateFormat`, see [[\yii\i18n\Formatter::dateFormat]] for details.
	 *
	 * Here are some example values:
	 *
	 * ```php
	 * 'MM/dd/yyyy' // date in ICU format
	 * 'php:m/d/Y' // the same date in PHP format
	 * ```
	 */
	public $format;

	/**
	 * @var string the locale ID that is used to localize the date parsing.
	 * This is only effective when the [PHP intl extension](http://php.net/manual/en/book.intl.php) is installed.
	 * If not set, the locale of the [[\yii\base\Application::formatter|formatter]] will be used.
	 * See also [[\yii\i18n\Formatter::locale]].
	 */
	public $locale;
	/**
	 * @var string the timezone to use for parsing date and time values.
	 * This can be any value that may be passed to [date_default_timezone_set()](http://www.php.net/manual/en/function.date-default-timezone-set.php)
	 * e.g. `UTC`, `Europe/Berlin` or `America/Chicago`.
	 * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
	 * If this property is not set, [[\yii\base\Application::timeZone]] will be used.
	 */
	public $timeZone;

	/**
	 * @var string the name of the attribute to be compared with
	 */
	public $compareAttribute;

	/**
	 * @var string the constant value to be compared with
	 */
	public $compareValue;

	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to false.
	 * If this is true, it means the attribute is considered valid when it is empty.
	 */
	public $allowEmpty = false;

	/**
	 * @var string the operator for comparison. Defaults to '='.
	 * The followings are valid operators:
	 * <ul>
	 * <li>'=' or '==': validates to see if the two values are equal;</li>
	 * <li>'!=': validates to see if the two values are NOT equal;</li>
	 * <li>'>': validates to see if the value being validated is greater than the value being compared with;</li>
	 * <li>'>=': validates to see if the value being validated is greater than or equal to the value being compared with;</li>
	 * <li>'<': validates to see if the value being validated is less than the value being compared with;</li>
	 * <li>'<=': validates to see if the value being validated is less than or equal to the value being compared with.</li>
	 * </ul>
	 */
	public $operator = '=';

	/**
	 * @var array map of short format names to IntlDateFormatter constant values.
	 */
	private $_dateFormats = [
		'short'  => 3, // IntlDateFormatter::SHORT,
		'medium' => 2, // IntlDateFormatter::MEDIUM,
		'long'   => 1, // IntlDateFormatter::LONG,
		'full'   => 0, // IntlDateFormatter::FULL,
	];

	public function init()
	{
		parent::init();

		if ($this->isEmpty($this->compareAttribute) && $this->isEmpty($this->compareValue)) {
			throw new Exception(Yii::t('common.common', 'You must specify compareAttribute or compareValue'));
		}
		if ($this->format === null) {
			$this->format = Yii::$app->formatter->dateFormat;
		}
		if ($this->locale === null) {
			$this->locale = Yii::$app->language;
		}
		if ($this->timeZone === null) {
			$this->timeZone = Yii::$app->timeZone;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function validateAttribute($model, $attribute)
	{
		$value = $model->$attribute;

		if ($this->allowEmpty && $this->isEmpty($value)) {
			return null;
		}

		$valueDT = ($this->parseDateValue($value)) ? new DateTime("@{$this->parseDateValue($value)}") : null;

		if ($this->compareValue != null) {
			$compareTo = $this->compareValue;
			$compareValue = $this->compareValue;
			$compareParsedValue = Yii::$app->formatter->asTimestamp($compareValue);
			$compareValueDT = ($compareParsedValue) ? new DateTime("@{$compareParsedValue}") : null;
		} else {
			$compareAttribute = $this->compareAttribute;
			$compareValue = $model->$compareAttribute;
			$compareTo = $model->getAttributeLabel($compareAttribute);
			$compareValueDT = ($this->parseDateValue($compareValue)) ? new DateTime("@{$this->parseDateValue($compareValue)}") : null;
		}
		
		if ($compareValueDT != null) {
			switch ($this->operator) {
				case '=':
					if ($valueDT != $compareValueDT) {
						$message = $this->message !== null ? $this->message : Yii::t('common.common', '{attribute} must be repeated exactly.');
					}
					break;
				case '!=':
					if ($valueDT == $compareValueDT) {
						$message = $this->message !== null ? $this->message : Yii::t('common.common', '{attribute} must not be equal to "{compareValue}".');
					}
					break;
				case '>':
					if ($valueDT <= $compareValueDT) {
						$message = $this->message !== null ? $this->message : Yii::t('common.common', '{attribute} must be greater than "{compareValue}".');
					}
					break;
				case '>=':
					if ($valueDT < $compareValueDT) {
						$message = $this->message !== null ? $this->message : Yii::t('common.common', '{attribute} must be greater than or equal to "{compareValue}".');
					}
					break;
				case '<':
					if ($valueDT >= $compareValueDT) {
						$message = $this->message !== null ? $this->message : Yii::t('common.common', '{attribute} must be less than "{compareValue}".');
					}
					break;
				case '<=':
					if ($valueDT > $compareValueDT) {
						$message = $this->message !== null ? $this->message : Yii::t('common.common', '{attribute} must be less than or equal to "{compareValue}".');
					}
					break;
				default:
					throw new Exception(Yii::t('common.common', 'Invalid operator "{operator}".', [
						'{operator}' => $this->operator
					]));
			}
		}

		if (!empty($message)) {
			$this->addError($model, $attribute, $message, [
				'compareAttribute' => $compareTo,
				'compareValue' => $compareValue
			]);
		}
	}

	/**
	 * Parses date string into UNIX timestamp
	 *
	 * @param string $value string representing date
	 * @return boolean|integer UNIX timestamp or false on failure
	 */
	protected function parseDateValue($value)
	{
		if (is_array($value)) {
			return false;
		}
		$format = $this->format;
		if (strncmp($this->format, 'php:', 4) === 0) {
			$format = substr($format, 4);
		} else {
			if (extension_loaded('intl')) {
				if (isset($this->_dateFormats[$format])) {
					$formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], IntlDateFormatter::NONE, $this->timeZone);
				} else {
					$formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $this->timeZone, null, $format);
				}
				// enable strict parsing to avoid getting invalid date values
				$formatter->setLenient(false);

				// There should not be a warning thrown by parse() but this seems to be the case on windows so we suppress it here
				// See https://github.com/yiisoft/yii2/issues/5962 and https://bugs.php.net/bug.php?id=68528
				$parsePos = 0;
				$parsedDate = @$formatter->parse($value, $parsePos);
				if ($parsedDate !== false && $parsePos === mb_strlen($value, Yii::$app ? Yii::$app->charset : 'UTF-8')) {
					return $parsedDate;
				}
				return false;
			} else {
				// fallback to PHP if intl is not installed
				$format = FormatConverter::convertDateIcuToPhp($format, 'date');
			}
		}
		$date = DateTime::createFromFormat($format, $value, new \DateTimeZone($this->timeZone));
		$errors = DateTime::getLastErrors();
		if ($date === false || $errors['error_count'] || $errors['warning_count']) {
			return false;
		} else {
			// if no time was provided in the format string set time to 0 to get a simple date timestamp
			if (strpbrk($format, 'HhGgis') === false) {
				$date->setTime(0, 0, 0);
			}
			return $date->getTimestamp();
		}
	}
}