<?php

namespace biz\inventory\controllers;

use Yii;
use biz\models\TransferHdr;
use biz\models\searchs\TransferHdr as TransferHdrSearch;
use biz\models\TransferDtl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \Exception;
use biz\tools\Hooks;
use yii\base\UserException;
use biz\base\Event;

/**
 * TransferController implements the CRUD actions for TransferHdr model.
 */
class ReceiveController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'receive-confirm' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all TransferHdr models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TransferHdrSearch;
        $params = Yii::$app->request->getQueryParams();
        $dataProvider = $searchModel->search($params);
        $dataProvider->query->andWhere('status > 1');

        return $this->render('index', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single TransferHdr model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /**
     * Updates an existing PurchaseHdr model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = TransferHdrSearch::SCENARIO_RECEIVE;
        Yii::$app->trigger(Hooks::E_IRUPD_1, new Event([$model]));
        list($details, $success) = $this->saveReceive($model);
        if ($success) {
            return $this->redirect(['view', 'id' => $model->id_transfer]);
        }
        return $this->render('update', [
                'model' => $model,
                'details' => $details,
                'masters' => $this->getDataMaster()
        ]);
    }

    /**
     * 
     * @param TransferHdr $model
     * @return array
     */
    protected function saveReceive($model)
    {
        $post = Yii::$app->request->post();
        $details = $model->transferDtls;
        $success = false;

        if ($model->load($post)) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $formName = (new TransferDtl)->formName();
                $postDetails = empty($post[$formName]) ? [] : $post[$formName];
                if ($postDetails === []) {
                    throw new Exception('Detail tidak boleh kosong');
                }
                $objs = [];
                foreach ($details as $detail) {
                    $objs[$detail->id_product] = $detail;
                }

                $model->status = TransferHdr::STATUS_DRAFT_RECEIVE;
                if ($model->save()) {
                    $success = true;
                    $id_hdr = $model->id_transfer;
                    $details = [];
                    foreach ($postDetails as $dataDetail) {
                        $id_dtl = $dataDetail['id_product'];
                        if (isset($objs[$id_dtl])) {
                            $detail = $objs[$id_dtl];
                            unset($objs[$id_dtl]);
                        } else {
                            $detail = new TransferDtl;
                        }

                        $detail->setAttributes($dataDetail);
                        $detail->id_transfer = $id_hdr;
                        if (!$detail->save()) {
                            $success = false;
                            $model->addError('', implode("\n", $detail->firstErrors));
                            break;
                        }
                        $details[] = $detail;
                    }
                    if ($success && count($objs)) {
                        $success = TransferDtl::deleteAll(['id_transfer' => $id_hdr, 'id_product' => array_keys($objs)]);
                    }
                }
                if ($success) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                }
            } catch (Exception $exc) {
                $model->addError('', $exc->getMessage());
                $transaction->rollBack();
                $success = false;
            }
            if (!$success) {
                $details = [];
                foreach ($postDetails as $value) {
                    $detail = new TransferDtl();
                    $detail->setAttributes($value);
                    $details[] = $detail;
                }
            }
        }
        return [$details, $success];
    }

    public function actionReceive($id)
    {
        $model = $this->findModel($id);
        Yii::$app->trigger(Hooks::E_IRREC_1, new Event([$model]));
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->status = TransferHdr::STATUS_RECEIVE;
            if (!$model->save()) {
                throw new UserException(implode(",\n", $model->firstErrors));
            }
            Yii::$app->trigger(Hooks::E_IRREC_21, new Event([$model]));
            foreach ($model->transferDtls as $detail) {
                Yii::$app->trigger(Hooks::E_IRREC_22, new Event([$model, $detail]));
            }
            Yii::$app->trigger(Hooks::E_IRREC_23, new Event([$model]));
            $transaction->commit();
        } catch (Exception $exc) {
            $transaction->rollBack();
            throw new UserException($exc->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the TransferHdr model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TransferHdr the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TransferHdr::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function getDataMaster()
    {
        $db = Yii::$app->db;
        $sql = "select p.id_product as id, p.cd_product as cd, p.nm_product as nm,
			u.id_uom, u.nm_uom, pu.isi
			from product p
			join product_uom pu on(pu.id_product=p.id_product)
			join uom u on(u.id_uom=pu.id_uom)
			order by p.id_product,pu.isi";
        $product = [];
        foreach ($db->createCommand($sql)->query() as $row) {
            $id = $row['id'];
            if (!isset($product[$id])) {
                $product[$id] = [
                    'id' => $row['id'],
                    'cd' => $row['cd'],
                    'text' => $row['nm'],
                    'id_uom' => $row['id_uom'],
                    'nm_uom' => $row['nm_uom'],
                ];
            }
            $product[$id]['uoms'][$row['id_uom']] = [
                'id' => $row['id_uom'],
                'nm' => $row['nm_uom'],
                'isi' => $row['isi']
            ];
        }

        // barcodes
        $barcodes = [];
        $sql_barcode = "select lower(barcode) as barcode,id_product as id"
            . " from product_child"
            . " union"
            . " select lower(cd_product), id_product"
            . " from product";
        foreach ($db->createCommand($sql_barcode)->queryAll() as $row) {
            $barcodes[$row['barcode']] = $row['id'];
        }

        $sql = "select id_warehouse,id_product,qty_stock from product_stock";
        $ps = [];
        foreach ($db->createCommand($sql)->queryAll() as $row) {
            $ps[$row['id_warehouse']][$row['id_product']] = $row['qty_stock'];
        }
        return [
            'product' => $product,
            'ps' => $ps
        ];
    }
}