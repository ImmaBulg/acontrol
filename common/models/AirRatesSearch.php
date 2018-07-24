<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\AirRates;
use yii\db\ActiveQuery;

/**
 * AirRatesSearch represents the model behind the search form about `common\models\AirRates`.
 */
class AirRatesSearch extends AirRates
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'rate_type_id', 'season', 'status', 'created_by', 'modified_by'], 'integer'],
            [['start_date', 'end_date', 'create_at', 'modified_at','startDate','endDate','fixed_payment'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function baseSearchQuery() {
        $query = AirRates::find();
        $query->andFilterWhere([
            'id' => $this->id,
            'rate_type_id' => $this->rate_type_id,
            'season' => $this->season,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'create_at' => $this->create_at,
            'modified_at' => $this->modified_at,
            'fixed_payment' => $this->fixed_payment,
            'created_by' => $this->created_by,
            'modified_by' => $this->modified_by,
        ]);
        return $query;
    }

    /**
    * Creates data provider instance with search query applied
    *
    * @param ActiveQuery $query
    *
    * @return ActiveDataProvider
    */
    public function search(ActiveQuery $query = null)
    {
        if($query === null) {
            $query = AirRates::find();
        }


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 15]
        ]);

        $dataProvider->sort->attributes+= [];

        return $dataProvider;
    }
}
