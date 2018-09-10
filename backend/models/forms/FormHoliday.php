<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.09.2018
 * Time: 15:45
 */

namespace backend\models\forms;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;

use common\helpers\Html;
use common\models\Holiday;
use common\components\i18n\Formatter;

class FormHoliday extends \yii\base\Model
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_EDIT = 'edit';

    private $_id;

    public $date;
    public $name;

    public function rules() {
        return [
            [['date', 'name'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'date' => Yii::t('backend.view', 'Holiday date'),
            'name' => Yii::t('backend.view', 'Holiday name'),
        ];
    }

    public function loadAttributes($scenario, $model)
    {
        switch ($scenario) {
            case self::SCENARIO_EDIT:
                $this->_id = $model->id;

                $this->date = $model->date;
                $this->name = $model->name;
                break;

            default:
                break;
        }
    }

    public function save()
    {
        $model = new Holiday();
        $model->date = date_format(date_create($this->date), 'Y-m-d');
        $model->name = $this->name;

        if (!$model->save()) {
            throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
        }

        return $model;
    }

    public function edit()
    {
        if (!$this->validate()) return false;

        $transaction = Yii::$app->db->beginTransaction();

        try	{
            $model = Holiday::findOne($this->_id);
            $model->date = date_format(date_create($this->date), 'Y-m-d');
            $model->name = $this->name;

            if (!$model->save()) {
                throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
            }

            $transaction->commit();
            return $model;
        } catch(Exception $e) {
            $transaction->rollback();
            throw new BadRequestHttpException($e->getMessage());
        }
    }

}