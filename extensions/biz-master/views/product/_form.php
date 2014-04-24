<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use biz\master\models\Category;
use biz\master\models\ProductGroups;
use yii\bootstrap\Modal;
use biz\master\models\Uom;
use biz\master\models\ProductUom;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var biz\master\models\Product $model
 * @var yii\widgets\ActiveForm $form
 */
?>
<?php $form = ActiveForm::begin(); ?>
<div class=" product col-lg-6">

    <div class="panel panel-primary">
        <div class="panel-heading">
            Product
        </div>
        <div class="panel-body">

            <?= $form->field($model, 'cd_product')->textInput(['maxlength' => 13, 'style' => 'width:160px;']) ?>

            <?= $form->field($model, 'nm_product')->textInput(['maxlength' => 64]) ?>

            <?= $form->field($model, 'id_category')->dropDownList(ArrayHelper::map(Category::find()->all(), 'id_category', 'nm_category'), ['style' => 'width:200px;']); ?>

            <?= $form->field($model, 'id_group')->dropDownList(ArrayHelper::map(ProductGroups::find()->all(), 'id_group', 'nm_group'), ['style' => 'width:200px;']); ?>

        </div>   

    </div>

</div>

<style>
    .tab-content {
        border: 1px #e0e0e0 solid;
        border-top: none;
        padding: 20px;
    }
</style>

<div class="col-lg-6">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs">
        <li class="active btn-finish"><a href="#uoms" data-toggle="tab">Uoms</a></li>
        <li><a href="#cogs" data-toggle="tab">Cogs</a></li>
        <li><a href="#price" data-toggle="tab">Prices</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane active" id="uoms">
            <?php
            if ($model->isNewRecord):
                echo $form->field($model, 'productUoms[id_uom]')->dropDownList(ArrayHelper::map(Uom::find()->all(), 'id_uom', 'nm_uom'), ['style' => 'width:200px;']);
                echo $form->field($model, 'productUoms[isi]')->textInput(['style' => 'width:120px;']);
            else:
                echo '<a class=" pull-right" data-toggle="modal" data-target="#myModal"><span class="glyphicon glyphicon-plus"></span></a>';
                $dPro = new ActiveDataProvider([
                    'query' => $model->getProductUoms(),
                    'pagination' => [
                        'pageSize' => 10,
                    ],
                ]);

                echo GridView::widget([
                    'dataProvider' => $dPro,
                    'tableOptions' => ['class' => 'table table-striped'],
                    'layout' => '{items}',
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'idUom.nm_uom',
                        'idUom.cd_uom',
                        'isi'
                    ],
                ]);

            endif;
            ?>
        </div>
        <div class="tab-pane" id="cogs">
            <?php
            if (!$model->isNewRecord):
                $dCogs = new ActiveDataProvider([
                    'query' => $model->getCogs(),
                    'pagination' => [
                        'pageSize' => 10,
                    ],
                ]);

                echo GridView::widget([
                    'dataProvider' => $dCogs,
                    'tableOptions' => ['class' => 'table table-striped'],
                    'layout' => '{items}',
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'idUom.nm_uom',
                        'idUom.cd_uom',
                        [ 'header' => 'Cogs',
                            'value' => function($model) {
                            return number_format($model->cogs, 2);
                        }]
                    ],
                ]);
            endif;
            ?>
        </div>
        <div class="tab-pane" id="price"></div>
    </div>
    <br>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
</div>
<?php
ActiveForm::end();

Modal::begin([
    'id' => 'myModal',
    'header' => '<h4 class="modal-title">Product Uoms</h4>@' . $model->nm_product
    ]);
$umodel = new ProductUom;
?>
<?php $form2 = ActiveForm::begin(); ?>
<div class="modal-body">
    <?= $form2->field($umodel, 'id_uom')->dropDownList(ArrayHelper::map(Uom::find()->all(), 'id_uom', 'nm_uom'), ['style' => 'width:200px;']); ?>
    <?= $form2->field($umodel, 'isi')->textInput(['style' => 'width:120px;']) ?>
</div>    
<div class="form-group modal-footer" style="text-align: right; padding-bottom: 0px;">
    <?= Html::submitButton($umodel->isNewRecord ? 'Create' : 'Update', ['class' => $umodel->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
</div>
<?php
ActiveForm::end();
Modal::end();