<?php

namespace api\models\forms;

use common\models\Meter;
use common\models\MeterChannel;
use yii\base\Model;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use Yii;

class FormUpdateMeterData extends Model
{

    public $meter_id;
    public $site_id;
    public $channels;

    private $_meter;
    private $_channels = array();

    public function rules()
    {
        return [
            [['meter_id', 'site_id','channels'], 'required'],
            [['meter_id', 'site_id'], 'integer'],
            [['channels'], 'validateChannels'],
            [['meter_id'], 'validateMeter']
        ];
    }

    public function validateChannels()
    {
        if (is_array($this->channels)) {
            foreach ($this->channels as $channel) {
                if (empty($channel['chanel_id'])) {
                    $this->addError('channels', 'Chanel id cannot be blank.');
                }
                if (!array_key_exists('is_main',$channel)) {
                    $this->addError('channels', 'Channel is main property not found.');
                }
                $this->_channels[$channel['chanel_id']] = $channel;
            }
        } else {
            $this->addError('channels', \Yii::t('api', 'Channels should be an array.'));
        }
    }

    public function validateMeter()
    {
        $this->_meter = Meter::find()->where(['id' => $this->meter_id])->andWhere(['site_id' => $this->site_id])->one();
        if (is_null($this->_meter)) {
            $this->addError('meter', 'Meter not found.');
        }
    }

    public function save()
    {

        if ($this->validate()) {

            $channels = MeterChannel::find()
                ->where(['meter_id' => $this->meter_id])
                ->andWhere(['in', 'id', array_keys($this->_channels)])
                ->indexBy('id')->all();

            $transaction = Yii::$app->db->beginTransaction();

            if (is_array($channels)) {
                try {
                    $result = [];
                    foreach ($channels as $channel_id => $channel) {
                        if (array_key_exists($channel_id,$this->_channels)) {
                            $channel->is_main = $this->_channels[$channel_id]['is_main'] ? 1 : 0;
                            if (!$channel->save()) {
                                throw new BadRequestHttpException(implode(' ', $channel->getFirstErrors()));
                            }
                            $result[] = $channel;
                        }
                    }
                    $transaction->commit();
                    return $result;
                } catch (Exception $e) {
                    $transaction->rollback();
                    throw new BadRequestHttpException($e->getMessage());
                }
            }
        }

        return false;
    }

}