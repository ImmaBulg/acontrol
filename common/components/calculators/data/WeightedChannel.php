<?php

namespace common\components\calculators\data;

use common\models\helpers\reports\ReportGenerator;
use common\models\MeterChannel;
use common\models\MeterSubchannel;
use Yii;
use yii\db\Query;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 21.07.2017
 * Time: 19:14
 */
class WeightedChannel
{
    private $channel_id = null;

    private $channel = null;


    /**
     * @return MeterChannel
     */
    public function getChannel() {
        if($this->channel === null) {
            $this->channel = MeterChannel::findOne($this->channel_id);
        }
        return $this->channel;
    }


    /**
     * @return null
     */
    public function getChannelId() {
        return $this->channel_id;
    }


    private $_subchannels = null;


    public function getSubchannels() {
        if($this->_subchannels !== null) {
            return $this->_subchannels;
        }
        else {
            $query = (new Query())
                ->select('t.channel')
                ->from(MeterSubchannel::tableName() . ' t')
                ->andWhere(['t.channel_id' => $this->channel_id]);
            $subchannels = Yii::$app->db->cache(function ($db) use ($query) {
                return $query->createCommand($db)->queryColumn();
            }, ReportGenerator::CACHE_DURATION);
        }
        return $subchannels;
    }


    /**
     * @param null $channel_id
     */
    public function setChannelId($channel_id) {
        $this->channel_id = $channel_id;
    }


    /**
     * @return null
     */
    public function getPercent() {
        return $this->percent;
    }


    /**
     * @param null $percent
     */
    public function setPercent($percent) {
        $this->percent = $percent;
    }


    private $percent = null;


    /**
     * WeightedChannel constructor.
     * @param null $channel_id
     * @param null $percent
     */
    public function __construct($channel_id, $percent) {
        $this->channel_id = $channel_id;
        $this->percent = $percent;
    }

}