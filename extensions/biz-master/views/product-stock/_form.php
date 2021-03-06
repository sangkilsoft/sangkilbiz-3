<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var biz\models\ProductStock $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="product-stock-form">

	<?php $form = ActiveForm::begin(); ?>

		<?= $form->field($model, 'opening_date')->textInput() ?>

		<?= $form->field($model, 'id_warehouse')->textInput() ?>

		<?= $form->field($model, 'id_product')->textInput() ?>

		<?= $form->field($model, 'id_uom')->textInput() ?>

		<?= $form->field($model, 'qty_stock')->textInput() ?>

		<?= $form->field($model, 'status_closing')->textInput() ?>

		<div class="form-group">
			<?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
		</div>

	<?php ActiveForm::end(); ?>

</div>
