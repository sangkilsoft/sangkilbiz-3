<?php

namespace biz\master\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use biz\master\models\Orgn;

/**
 * OrgnSearch represents the model behind the search form about Orgn.
 */
class OrgnSearch extends Model
{
	public $id_orgn;
	public $cd_orgn;
	public $nm_orgn;
	public $create_date;
	public $create_by;
	public $update_date;
	public $update_by;

	public function rules()
	{
		return [
			[['id_orgn', 'create_by', 'update_by'], 'integer'],
			[['cd_orgn', 'nm_orgn', 'create_date', 'update_date'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id_orgn' => 'Id Orgn',
			'cd_orgn' => 'Cd Orgn',
			'nm_orgn' => 'Nm Orgn',
			'create_date' => 'Create Date',
			'create_by' => 'Create By',
			'update_date' => 'Update Date',
			'update_by' => 'Update By',
		];
	}

	public function search($params)
	{
		$query = Orgn::find();
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$this->addCondition($query, 'id_orgn');
		$this->addCondition($query, 'cd_orgn', true);
		$this->addCondition($query, 'nm_orgn', true);
		$this->addCondition($query, 'create_date', true);
		$this->addCondition($query, 'create_by');
		$this->addCondition($query, 'update_date', true);
		$this->addCondition($query, 'update_by');
		return $dataProvider;
	}

	protected function addCondition($query, $attribute, $partialMatch = false)
	{
		$value = $this->$attribute;
		if (trim($value) === '') {
			return;
		}
		if ($partialMatch) {
			$query->andWhere(['like', $attribute, $value]);
		} else {
			$query->andWhere([$attribute => $value]);
		}
	}
}