<?php

use yii\web\JsExpression;
use yii\jui\AutoComplete;
use yii\helpers\Html;
use backend\modules\purchase\models\PurchaseDtl;
use backend\modules\master\models\Product;
?>
<div class="col-lg-9" style="padding-left: 0px;">
    <div class="panel panel-info">
        <div class="panel-heading">
            Product :
            <?php
            echo AutoComplete::widget([
                'name' => 'product',
                'id' => 'product',
                'clientOptions' => [
                    'source' => new JsExpression('yii.sales.sourceProduct'),
                    'select' => new JsExpression('yii.sales.onProductSelect'),
                    'delay' => 100,
                ]
            ]);
            ?>
        </div>
        <div class="panel-body" style="text-align: right;">
            <input type="hidden" data-field="total_price"><h2>Rp<span id="total-price"></h2></span>
        </div>
        <table id="detail-grid" class="table table-striped">
            <?php

            function renderRow($model, $index) {
                ob_start();
                ob_implicit_flush(false);
                ?>
                <tr>
                    <td style="width: 50px">
                        <a data-action="delete" title="Delete" href="#"><span class="glyphicon glyphicon-trash"></span></a>
                        <?= Html::activeHiddenInput($model, "[$index]id_product", ['data-field' => 'id_product', 'id' => false]) ?>
                        <?= Html::activeHiddenInput($model, "[$index]id_purchase_dtl", ['data-field' => 'id_purchase_dtl', 'id' => false]) ?>
                    </td>
                    <td class="items" style="width: 45%">
                        <ul class="nav nav-list">
                            <li><span class="cd_product"><?= Html::getAttributeValue($model, 'idProduct[cd_product]') ?></span> 
                                - <span class="nm_product"><?= Html::getAttributeValue($model, 'idProduct[nm_product]') ?></span></li>
                            <li>
                                Jumlah <?=
                                Html::activeTextInput($model, "[$index]purch_qty", [
                                    'data-field' => 'purch_qty',
                                    'size' => 5, 'id' => false,
                                    'required' => true])
                                ?>
                                <?= Html::activeDropDownList($model, "[$index]id_uom", Product::ListUoms($model->id_product), ['data-field' => 'id_uom', 'id' => false]) ?>
                            </li>
                            <li>
                                Price Rp <?=
                                Html::activeTextInput($model, "[$index]purch_price", [
                                    'data-field' => 'purch_price',
                                    'size' => 16, 'id' => false,
                                    'required' => true])
                                ?>
                            </li>
                        </ul>
                    </td>
                    <td class="selling" style="width: 40%">
                        <ul class="nav nav-list">
                            <li>Selling Price</li>
                            <li>
                                <?php
                                $purch_price = $model->purch_price;
                                $selling_price = $model->selling_price;
                                $markup = $selling_price > 0 ? 100 * ($selling_price - $purch_price) / $selling_price : 0;
								$markup = round($markup, 2);
                                ?>
                                Markup <?=
                                Html::textInput('', $markup, [
                                    'data-field' => 'markup_price',
                                    'size' => 8, 'id' => false,
                                    'required' => true])
                                ?> %
                            </li>
                            <li>
                                Price Rp <?=
                                Html::activeTextInput($model, "[$index]selling_price", [
                                    'data-field' => 'selling_price',
                                    'size' => 16, 'id' => false,
                                    'required' => true])
                                ?>
                            </li>
                        </ul>
                    </td>
                    <td class="total-price">
                        <ul class="nav nav-list">
                            <li>&nbsp;</li>
                            <li>
                                <input type="hidden" data-field="total_price"><span class="total-price"></span>
                            </li>
                        </ul>
                    </td>
                </tr>
                <?php
                return trim(preg_replace('/>\s+</', '><', ob_get_clean()));
            }
            ?>
            <?php
            $rows = [];
            foreach ($details as $index => $model) {
                $rows[] = renderRow($model, $index);
            }
            echo Html::tag('tbody', implode("\n", $rows), ['data-template' => renderRow(new PurchaseDtl, '_index_')])
            ?>
        </table>
    </div>
</div>
<?php
$this->render('_script');