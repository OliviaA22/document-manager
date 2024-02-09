<?php

namespace humhub\modules\documentmanager\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use humhub\modules\documentmanager\models\Document;


/**
 * DocumentSearch represents the model behind the search form of `humhub\modules\documentmanager\models\Document`.
 */
class DocumentSearch extends Document
{
    public $search;
    public $fk_affiliation;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['search'], 'safe'],
            [['fk_affiliation'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Document::find();
        // $dataProvider = new ActiveDataProvider([
        //     'query' => $query,
        // ]);

        $this->load($params);

        // if (!$this->validate()) {
        //     // uncomment the following line if you do not want to return any records when validation fails
        //     // $query->where('0=1');
        //     return $dataProvider;
        // }


        $query->andFilterWhere(['or', ['like', 'name', $this->search], ['like', 'tags', $this->search]]);
        
        $query->andFilterWhere(['fk_affiliation' => $this->fk_affiliation]);

        // grid filtering conditions

        /*
        $query->andFilterWhere(['like', 'document.name', $this->name])
            ->andFilterWhere(['like', 'tags', $this->tags]);
            */
        return $query;
    }
}

