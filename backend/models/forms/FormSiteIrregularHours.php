<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 13.08.2018
 * Time: 11:42
 */

namespace backend\models\forms;


use common\models\SiteIrregularHours;

use Yii;
use yii\base\Model;
use yii\web\BadRequestHttpException;

class FormSiteIrregularHours extends Model
{
    public $site_id;
    public $data = array();
    private $_data = array();

    public function rules() {
        return [
            [['site_id'], 'string'],
            [['data'], 'validateData']
        ];
    }

    public static function validateTimeString($time) {
        return preg_match('/\d{2}:\d{2}:\d{2}/', $time);
    }

    public function validateData() {
        foreach ($this->data as $row) {
            if (self::validateTimeString($row['hours_from']) && self::validateTimeString($row['hours_to'])) {
                $this->_data[] = $row;
            }
        }
    }

    public function save() {
        if ($this->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $irregular_models = SiteIrregularHours::find()->where(['site_id' => $this->site_id])->indexBy('id')->all();
                $models_to_insert = [];
                foreach ($this->_data as $key => $row) {
                    if (array_key_exists($row['id'], $irregular_models)) {
                        $model = $irregular_models[$row['id']];
                        $model->load($row, '');
                        if ($model->oldAttributes !== $model->attributes) {
                            if ($model->save()) {
                                throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
                            }
                        }
                        unset($irregular_models[$row['id']]);
                    } else {
                        $model = new SiteIrregularHours();
                        $model->load($row, '');
                        unset($row['id']);
                        if ($model->validate()) {
                            $models_to_insert[] = [
                                'site_id' => $row['site_id'],
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
                    SiteIrregularHours::deleteAll(['id' => $models_to_delete]);
                }

                if ($models_to_insert) {
                    Yii::$app->db->createCommand()->batchInsert(
                        SiteIrregularHours::tableName(),
                        [
                            'site_id',
                            'day_number',
                            'hours_from',
                            'hours_to',
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