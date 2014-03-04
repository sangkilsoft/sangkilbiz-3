<?php

use yii\helpers\Html;
use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var backend\modules\inventory\models\TransferHdrSearch $searchModel
 */

$this->title = 'Transfer Hdrs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transfer-hdr-index">

	<h1><?= Html::encode($this->title) ?></h1>

	<?php // echo $this->render('_search', ['model' => $searchModel]); ?>

	<?php echo GridView::widget([
		'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'columns' => [
			['class' => 'yii\grid\SerialColumn'],
			'transfer_num',
			'idWarehouseSource.nm_whse',
			'idWarehouseDest.nm_whse',
			'nmStatus',
			[
				'class' => 'yii\grid\ActionColumn',
				'template' => '{view} {update}'],
		],
	]); ?>

</div>
