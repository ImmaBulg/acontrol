<?php

namespace backend\models\forms;

use common\models\TenantIrregularHours;
use yii\base\Model;
use Yii;
use yii\db\Exception;
use yii\web\BadRequestHttpException;

class FormIrregularHours extends Model
{

    public $tenant_id;
    public $data = array();
    private $_data = array();

    public function rules()
    {
        return [
            [['tenant_id'], 'integer'],
            [['data'], 'validateData'],
        ];
    }

    public static function validateTimeString($time)
    {
        return preg_match('/\d{2}:\d{2}:\d{2}/', $time);
    }

    public function validateData()
    {
        foreach ($this->data as $row) {
            if (self::validateTimeString($row['hours_from']) && self::validateTimeString($row['hours_to'])) {
                $this->_data[] = $row;
            }
        }
    }

    public function save()
    {
        if ($this->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $irregular_models = TenantIrregularHours::find()->where(['tenant_id' => $this->tenant_id])->indexBy('id')->all();
                $tenant_id = $this->tenant_id;
                $models_to_insert = [];
                //return [$irregular_models, $this->_data];

                foreach ($this->_data as $key => $row) {
                    if (array_key_exists($row['id'], $irregular_models)) {
                        $model = $irregular_models[$row['id']];
                        $model->load($row,'');
                        if ($model->oldAttributes != $model->attributes) {
                            if (!$model->save()) {
                                throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
                            }
                        }
                        unset($irregular_models[$row['id']]);
                    } else {
                        $model = new TenantIrregularHours();
                        $model->load($row,'');
                        unset($row['id']);
                        if ($model->validate()) {
                            $models_to_insert[] = [
                                'tenant_id' => $tenant_id,
                                'day_number' => $row['day_number'],
                                'hours_from' => $row['hours_from'],
                                'hours_to' => $row['hours_to']
                            ];
                        } else {
                            throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
                        }
                    }
                }
                $models_to_delete = array_keys($irregular_models);

                if ($models_to_delete) {
                    TenantIrregularHours::deleteAll(['id' => $models_to_delete]);
                }
                if ($models_to_insert) {
                    Yii::$app->db->createCommand()->batchInsert(
                        TenantIrregularHours::tableName(),
                        [
                            'tenant_id',
                            'day_number',
                            'hours_from',
                            'hours_to'
                        ],
                        $models_to_insert
                    )->execute();
                }

                $transaction->commit();
                return true;
            } catch (Exception $e) {
                $transaction->rollBack();
                throw new BadRequestHttpException($e->getMessage());
            }
        }

        return false;
    }


}