<?php

namespace humhub\modules\documentmanager\models\search;

use humhub\modules\content\components\ContentContainerSettingsManager;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use humhub\modules\documentmanager\models\DocumentRevision;
use humhub\modules\documentmanager\models\SettingsForm;
use humhub\modules\documentmanager\notifications\DocumentNotification;
use humhub\modules\user\models\User;
use Yii;
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

            [['document_content', 'name', 'tags', 'search', 'version', 'created_date', 'fk_affiliation'], 'safe'],
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


            return $dataProvider;
        }
        $revisions = DocumentRevision::getSearchRevisions($this)->all();     

        $dataProvider = new ArrayDataProvider([
            'allModels' => $revisions,
            'pagination' => false,
        ]);

        return $dataProvider;
    }



}
