<?php namespace api\models\forms;

use common\models\AirMeterRawData;
use DateTime;
use Yii;
use yii\web\BadRequestHttpException;
use common\components\i18n\Formatter;

/**
 * FormElectricityMeterRawData is the class for meter raw data create/edit.
 */
class FormAirMeterRawData extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	public $data;

	public function rules()
	{
		return [
			//[['data'], 'required'],
			[['data'], 'validateData'],
		];
	}

	public function validateData($attribute)
	{
		$values = [];

		if (!is_array($this->$attribute)) {
			return $this->addError($attribute, Yii::t('api.meter', '{attribute} must be an array.', [
				'attribute' => $this->getAttributeLabel($attribute),
			]));
		}

		foreach ($this->$attribute as &$data) {
			if (!is_array($data)) {
				return $this->addError($attribute, Yii::t('api.meter', '{attribute} elements must be an array.', [
					'attribute' => $this->getAttributeLabel($attribute),
				]));
			}

			$form = new FormAirMeterRawDataSingle();
			$form->attributes = $data;

			if (!$form->validate()) {
				throw new BadRequestHttpException(implode(' ', $form->getFirstErrors()));
			}
			$data = $form->attributes;
		}

		return $this->$attribute;
	}

	public function attributeLabels()
	{
		return [
			'data' => Yii::t('api.meter', 'Data'),
		];
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$sql_date_format = Formatter::SQL_DATE_FORMAT;
		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$models = [];

			foreach ($this->data as $data) {
			    $date = new DateTime($data['datetime']);
			    $date = $date->setTime((int)$date->format('H') + 1, 0, 0);
			    $data['datetime'] = $date->format('Y-m-d H:i:s');
				$model = AirMeterRawData::find()
				->andWhere([
					'meter_id' => $data['meter_id'],
					'channel_id' => $data['channel_id'],
				])->andWhere("DATE_FORMAT(FROM_UNIXTIME(datetime), '$sql_date_format') = :datetime", [
					'datetime' => $data['datetime'],
				])->one();

				if ($model == null) {
					$model = new AirMeterRawData();
					$model->meter_id = $data['meter_id'];
					$model->channel_id = $data['channel_id'];
				}

				$model->attributes = $data;

				if (!$model->save()) {
				    $error_string = self::recursive_implode($model->getErrors(),' ',false,false);
					throw new BadRequestHttpException($error_string);
				}

                AirMeterRawData::deleteCacheValue(["meter_raw_data:{$model->relationMeter->name}_{$model->relationMeterChannel->channel}"]);

				$models[] = $model;
			}

			$transaction->commit();
			return $this->data;
		} catch(\Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}

    /**
     * Recursively implodes an array with optional key inclusion
     *
     * Example of $include_keys output: key, value, key, value, key, value
     *
     * @access  public
     * @param   array $array multi-dimensional array to recursively implode
     * @param   string $glue value that glues elements together
     * @param   bool $include_keys include keys before their values
     * @param   bool $trim_all trim ALL whitespace from string
     * @return  string  imploded array
     */
    public static function recursive_implode(array $array, $glue = ',', $include_keys = false, $trim_all = true) {
        $glued_string = '';
        // Recursively iterates array and adds key/value to glued string
        array_walk_recursive($array, function ($value, $key) use ($glue, $include_keys, &$glued_string) {
            $include_keys and $glued_string .= $key . $glue;
            $glued_string .= $value . $glue;
        });
        // Removes last $glue from string
        strlen($glue) > 0 and $glued_string = substr($glued_string, 0, -strlen($glue));
        // Trim ALL whitespace
        $trim_all and $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);
        return (string)$glued_string;
    }
}
