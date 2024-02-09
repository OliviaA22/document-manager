<?php

namespace humhub\modules\documentmanager\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use humhub\modules\documentmanager\models\Folder;

/**
 * FolderSearch represents the model behind the search form of `humhub\modules\documentmanager\models\Folder`.
 */
class FolderSearch extends Folder
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'fk_folder', 'root_folder', 'sub_level'], 'integer'],
            [['name'], 'safe'],
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
        $query = Folder::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'fk_folder' => $this->fk_folder,
            'root_folder' => $this->root_folder,
            'sub_level' => $this->sub_level,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
