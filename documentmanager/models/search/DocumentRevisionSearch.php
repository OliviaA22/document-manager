<?php

namespace humhub\modules\documentmanager\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use humhub\modules\documentmanager\models\DocumentRevision;

use yii\data\ArrayDataProvider;

/**
 * RevisionSearch represents the model behind the search form of `humhub\modules\documentmanager\models\Revision`.
 */
class DocumentRevisionSearch extends DocumentRevision
{

     public $search;
    public $fk_affiliation;
    public $tags;
    public $name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'fk_document', 'is_visible', 'is_informed'], 'integer'],

            [['document_content', 'name', 'tags','search', 'version','created_date', 'fk_affiliation'], 'safe'],
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
        $query = DocumentRevision::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');


            // echo "<pre>";
            // print_r($this->getErrors());
            // echo "</pre>";
            // die;

            return $dataProvider;
        }


        $revisions = DocumentRevision::getSearchRevisions($this);

            // echo "<pre>";
            // print_r($revisions);
            // echo "</pre>";
            // die;        

        $dataProvider = new ArrayDataProvider([
            'allModels' => $revisions,
            'pagination' => false,
        ]);

        // $query->andFilterWhere(['or', ['like', 'name', $this->search], ['like', 'tags', $this->search]]);
        
        // $query->andFilterWhere(['fk_affiliation' => $this->fk_affiliation]);


        // $query->andFilterWhere(['like', 'tags', $this->tags])
        //     ->andFilterWhere(['like', 'fk_affiliation', $this->fk_affiliation]);
        

        

        return $dataProvider;
    }
}
