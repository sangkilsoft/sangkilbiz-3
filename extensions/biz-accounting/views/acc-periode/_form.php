<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;

/**
 * @var yii\web\View $this
 * @var biz\models\\AccPeriode $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="acc-periode-form">

	<?php $form = ActiveForm::begin(); ?>

	<?= $form->field($model, 'nm_periode')->textInput(['maxlength' => 32]) ?>

	<?=
	$form->field($model, 'date_from')->widget(DatePicker::className(), [
		'options' => ['class' => 'form-control'],
		'clientOptions' => [
			'dateFormat' => 'yy-mm-dd',
		],])
	?>

<?= $form->field($model, 'date_to')->widget(DatePicker::className(), [
	'options' => ['class' => 'form-control'],
		'clientOptions' => [
			'dateFormat' => 'yy-mm-dd',
		],])
?>

	<div class="form-group">
	<?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	</div>

<?php ActiveForm::end(); ?>

</div>
