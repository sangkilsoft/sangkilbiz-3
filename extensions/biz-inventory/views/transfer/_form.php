<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use biz\tools\Helper;
use biz\inventory\components\TransferAsset;
use biz\tools\BizDataAsset;

/**
 * @var yii\web\View $this
 * @var biz\purchase\models\PurchaseHdr $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="purchase-hdr-form">
    <?php
    $form = ActiveForm::begin([
            'id' => 'transfer-form',
    ]);
    ?>
    <?php
    $models = $details;
    array_unshift($models, $model);
    echo $form->errorSummary($models)
    ?>
<?= $this->render('_detail', ['model' => $model, 'details' => $details]) ?> 
    <div class="col-lg-3" style="padding-right: 0px;">
        <div class="panel panel-primary">
            <div class="panel-heading">
                Transfer Header
            </div>
            <div class="panel-body">
                <?= $form->field($model, 'transfer_num')->textInput(['maxlength' => 16, 'readonly' => true]); ?>
                <?= $form->field($model, 'id_warehouse_source')->dropDownList(Helper::getWarehouseList()); ?>
                <?= $form->field($model, 'id_warehouse_dest')->dropDownList(Helper::getWarehouseList()); ?>
                <?php
                echo $form->field($model, 'transferDate')
                    ->widget('yii\jui\DatePicker', [
                        'options' => ['class' => 'form-control', 'style' => 'width:50%'],
                        'clientOptions' => [
                            'dateFormat' => 'dd-mm-yy'
                        ],
                ]);
                ?>
            </div>
        </div>
        <div class="form-group">
            <?php
            echo Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']);
            ?>
        </div>
    </div>
<?php ActiveForm::end(); ?>

</div>
<?php
TransferAsset::register($this);
BizDataAsset::register($this, [
    'master'=>$masters
]);
$js_ready = '$("#product").data("ui-autocomplete")._renderItem = yii.global.renderItem;';
$this->registerJs($js_ready);
