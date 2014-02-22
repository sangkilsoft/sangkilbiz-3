<?php

namespace backend\modules\purchase\models;

/**
 * This is the model class for table "purchase_hdr".
 *
 * @property integer $id_purchase_hdr
 * @property string $purchase_num
 * @property integer $id_supplier
 * @property integer $id_warehouse
 * @property string $purchase_date
 * @property integer $id_status
 * @property string $update_date
 * @property integer $update_by
 * @property integer $create_by
 * @property string $create_date
 *
 * @property PurchaseDtl[] $purchaseDtls
 * @property Supplier $idSupplier
 * @property Warehouse $idWarehouse
 */
class PurchaseHdr extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'purchase_hdr';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['purchase_num', 'id_supplier', 'id_warehouse', 'purchase_date', 'id_status'], 'required'],
			[['id_supplier', 'id_warehouse', 'id_status'], 'integer'],
			[['purchase_date'], 'safe'],
			[['purchase_num'], 'string', 'max' => 16]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id_purchase_hdr' => 'Id Purchase Hdr',
			'purchase_num' => 'Purchase Num',
			'id_supplier' => 'Id Supplier',
			'id_warehouse' => 'Id Warehouse',
			'purchase_date' => 'Purchase Date',
			'id_status' => 'Id Status',
			'update_date' => 'Update Date',
			'update_by' => 'Update By',
			'create_by' => 'Create By',
			'create_date' => 'Create Date',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getPurchaseDtls()
	{
		return $this->hasMany(PurchaseDtl::className(), ['id_purchase_hdr' => 'id_purchase_hdr']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getIdSupplier()
	{
		return $this->hasOne(Supplier::className(), ['id_supplier' => 'id_supplier']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getIdWarehouse()
	{
		return $this->hasOne(Warehouse::className(), ['id_warehouse' => 'id_warehouse']);
	}
}