<?php

namespace biz\sales\models;

use Yii;
use biz\master\models\Customer;
use biz\master\models\Branch;
use biz\master\models\Warehouse;

/**
 * This is the model class for table "sales_hdr".
 *
 * @property integer $id_sales
 * @property string $sales_num
 * @property integer $id_branch
 * @property integer $id_customer
 * @property integer $id_cashdrawer
 * @property string $discount
 * @property string $sales_date
 * @property integer $status
 * @property string $create_date
 * @property integer $create_by
 * @property string $update_date
 * @property integer $update_by
 *
 * @property Cashdrawer $idCashdrawer
 * @property Customer $idCustomer
 * @property Branch $idBranch
 * @property SalesDtl[] $salesDtls
 */
class SalesHdr extends \yii\db\ActiveRecord
{

	const STATUS_DRAFT = 1;
	const STATUS_RELEASE = 2;
	const STATUS_CLOSE = 10;

	public $id_warehouse;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'sales_hdr';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id_customer'], 'filter', 'filter' => function($val) {
					return empty($val) ? null : (int) $val;
				}],
			[['id_branch', 'id_customer', 'sales_date', 'status'], 'required'],
			[['id_branch', 'id_cashdrawer', 'status', 'id_warehouse'], 'integer'],
			[['discount', 'sales_date'], 'string'],
			[['sales_num'], 'string', 'max' => 16]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id_sales' => 'Id Sales',
			'sales_num' => 'Sales Num',
			'id_branch' => 'Id Branch',
			'id_customer' => 'Id Customer',
			'id_cashdrawer' => 'Id Cashdrawer',
			'discount' => 'Discount',
			'sales_date' => 'Sales Date',
			'status' => 'Status',
			'create_date' => 'Create Date',
			'create_by' => 'Create By',
			'update_date' => 'Update Date',
			'update_by' => 'Update By',
		];
	}

	public function getNmStatus()
	{
		$map = [
			self::STATUS_DRAFT => 'Draft',
			self::STATUS_RELEASE => 'Release',
			self::STATUS_CLOSE => 'Close',
		];
		return $map[$this->status];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getIdCashdrawer()
	{
		return $this->hasOne(Cashdrawer::className(), ['id_cashdrawer' => 'id_cashdrawer']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getIdCustomer()
	{
		return $this->hasOne(Customer::className(), ['id_customer' => 'id_customer']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getIdBranch()
	{
		return $this->hasOne(Branch::className(), ['id_branch' => 'id_branch']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getIdWarehouse()
	{
		return $this->hasOne(Warehouse::className(), ['id_warehouse' => 'id_warehouse']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getSalesDtls()
	{
		return $this->hasMany(SalesDtl::className(), ['id_sales' => 'id_sales']);
	}

	public function behaviors()
	{
		return [
			'app\tools\AutoTimestamp',
			'app\tools\AutoUser',
			[
				'class' => 'mdm\autonumber\Behavior',
				'digit' => 4,
				'group' => 'sales',
				'attribute' => 'sales_num',
				'value' => function($event) {
					return date('ymd.?');
				}
			]
		];
	}

}