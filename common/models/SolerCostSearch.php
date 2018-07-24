<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SolerCost;
use yii\db\ActiveQuery;

/**
 * SolerCostSearch represents the model behind the search form about `common\models\SolerCost`.
 */
class SolerCostSearch extends SolerCost
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['from_date', 'to_date'], 'safe'],
            [['cost'], 'number'],
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
        $query = SolerCost::find();
        $query->andFilterWhere([
            'id' => $this->id,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
            'cost' => $this->cost,
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
            $query = SolerCost::find();
        }


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 15]
        ]);

        $dataProvider->sort->attributes+= [];

        return $dataProvider;
    }
}
