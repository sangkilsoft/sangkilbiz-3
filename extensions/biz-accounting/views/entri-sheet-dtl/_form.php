<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var biz\models\\EntriSheetDtl $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="entri-sheet-dtl-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id_esheet')->textInput() ?>

    <?= $form->field($model, 'id_coa')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
