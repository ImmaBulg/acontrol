<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.09.2018
 * Time: 15:52
 */

namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\Holiday;

class SearchHoliday extends Search
{
    public $modelClass = '\backend\models\searches\models\Holiday';

    public function getDefaultQuery()
    {
        $modelClass = $this->modelClass;
        $t = $modelClass::tableName();
        $query = $modelClass::find();

        return $query;
    }

    public function getDefaultSort()
    {
        $modelClass = $this->modelClass;

        return [
            'sortParam' => $modelClass::SORT_PARAM,
            'defaultOrder' => [
                'date' => SORT_ASC,
            ],
            'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
            'attributes' => [
                'id' => [
                    'asc' => ['id' => SORT_ASC],
                    'desc' => ['id' => SORT_DESC],
                ],
                'name' => [
                    'asc' => ['name' => SORT_ASC],
                    'desc' => ['name' => SORT_DESC],
                ],
                'date' => [
                    'asc' => ['date' => SORT_ASC],
                    'desc' => ['date' => SORT_DESC],
                ],
            ],
        ];
    }

    public function setFilters()
    {
        $filters = $this->getFilterParameters();

        if ($filters != null) {
            $modelClass = $this->modelClass;
            $t = $modelClass::tableName();
            $query = $this->getQuery();
            $model = $this->getModel();

            foreach ($filters as $attribute => $value) {
                if ($value != null) {
                    switch ($attribute) {
                        /*
                         * ID
                         */
                        case 'id':
                            $query->andFilterWhere(['like', "$t.id", $value. '%', false]);
                            break;

                        /*
                         * Name
                         */
                        case 'name':
                            $query->andFilterWhere(["$t.name" => $value]);
                            break;

                        /*
                         * Date
                         */
                        case 'date':
                            $query->andFilterWhere(["$t.date" => $value]);
                            break;

                        default:
                            break;
                    }
                }
            }
        }
    }
}