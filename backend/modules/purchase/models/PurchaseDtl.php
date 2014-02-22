<?php

namespace backend\modules\purchase\models;

/**
 * This is the model class for table "purchase_dtl".
 *
 * @property string $id_purchase_dtl
 * @property integer $id_purchase_hdr
 * @property integer $id_product
 * @property integer $id_supplier
 * @property string $purch_price
 * @property string $purch_qty
 * @property integer $id_uom
 * @property string $update_date
 * @property integer $update_by
 * @property integer $create_by
 * @property string $create_date
 *
 * @property Uom $idUom
 * @property Supplier $idSupplier
 * @property Product $idProduct
 * @property PurchaseHdr $idPurchaseHdr
 */
class PurchaseDtl extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'purchase_dtl';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id_purchase_hdr', 'id_product', 'id_supplier', 'purch_qty', 'id_uom'], 'required'],
			[['id_purchase_hdr', 'id_product', 'id_supplier', 'id_uom'], 'integer'],
			[['purch_price', 'purch_qty'], 'string']
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id_purchase_dtl' => 'Id Purchase Dtl',
			'id_purchase_hdr' => 'Id Purchase Hdr',
			'id_product' => 'Id Product',
			'id_supplier' => 'Id Supplier',
			'purch_price' => 'Purch Price',
			'purch_qty' => 'Purch Qty',
			'id_uom' => 'Id Uom',
			'update_date' => 'Update Date',
			'update_by' => 'Update By',
			'create_by' => 'Create By',
			'create_date' => 'Create Date',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getIdUom()
	{
		return $this->hasOne(Uom::className(), ['id_uom' => 'id_uom']);
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
	public function getIdProduct()
	{
		return $this->hasOne(Product::className(), ['id_product' => 'id_product']);
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getIdPurchaseHdr()
	{
		return $this->hasOne(PurchaseHdr::className(), ['id_purchase_hdr' => 'id_purchase_hdr']);
	}
}