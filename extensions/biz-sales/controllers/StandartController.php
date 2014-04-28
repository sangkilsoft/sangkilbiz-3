<?php

namespace biz\sales\controllers;

use Yii;
use biz\sales\models\SalesHdr;
use biz\sales\models\SalesHdrSearch;
use biz\sales\models\SalesDtl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\HttpCache;
use mdm\tools\AppCache;
use biz\master\models\Cogs;
use \Exception;
use yii\base\UserException;
use biz\inventory\models\ProductStock;
use biz\master\models\ProductUom;
use biz\master\models\GlobalConfig;
use app\tools\Helper;
use app\tools\Hooks;

/**
 * PosController implements the CRUD actions for SalesHdr model.
 */
class StandartController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'release' => ['post']
                ],
            ],
            'httpCache' => [
                'class' => HttpCache::className(),
                'only' => ['js'],
                'lastModified' => function ($action, $params) {
                return 50000 * ((int) (time() / 50000)) - 36000;
            },
                'etagSeed' => function($action, $params) {
                return 1;
            }
            ],
        ];
    }

    /**
     * Lists all SalesHdr models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SalesHdrSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single SalesHdr model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
                'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new SalesHdr model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $payment_methods = [
            1 => 'Cash',
            2 => 'Bank',
        ];
        $model = new SalesHdr;
        $model->id_branch = Yii::$app->user->identity->id_branch;
        $model->status = 1;
        $model->id_customer = 1;
        $model->sales_date = date('Y-m-d');
        list($details, $success) = $this->saveSales($model);
        if ($success) {
            return $this->redirect(['view', 'id' => $model->id_sales]);
        }
        $model->setIsNewRecord(true);
        return $this->render('create', [
                'model' => $model,
                'details' => $details,
                'payment_methods' => $payment_methods]);
    }

    /**
     * 
     * @param SalesHdr $model
     * @return array
     */
    protected function saveSales($model)
    {
        $post = Yii::$app->request->post();
        $details = $model->salesDtls;
        $success = false;

        if ($model->load($post)) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $formName = (new SalesDtl)->formName();
                $postDetails = empty($post[$formName]) ? [] : $post[$formName];
                if ($postDetails === []) {
                    throw new Exception('Detail tidak boleh kosong');
                }
                $objs = [];
                foreach ($details as $detail) {
                    $objs[$detail->id_sales_dtl] = $detail;
                }
                if ($model->save()) {
                    $success = true;
                    $id_hdr = $model->id_sales;
                    $id_whse = $model->id_warehouse;
                    $details = [];
                    foreach ($postDetails as $dataDetail) {
                        $id_dtl = $dataDetail['id_sales_dtl'];
                        if (isset($objs[$id_dtl])) {
                            $detail = $objs[$id_dtl];
                            unset($objs[$id_dtl]);
                        } else {
                            $detail = new SalesDtl;
                        }

                        $detail->setAttributes($dataDetail);
                        $detail->id_sales = $id_hdr;
                        $detail->id_warehouse = $id_whse;
                        $cogs = Cogs::findOne(['id_product' => $detail->id_product]);
                        if ($cogs) {
                            $detail->cogs = $cogs->cogs;
                        } else {
                            $detail->cogs = 0;
                        }
                        if (!$detail->save()) {
                            $success = false;
                            break;
                        }

                        $details[] = $detail;
                    }
                    if ($success && count($objs) > 0) {
                        $success = SalesDtl::deleteAll(['id_sales_dtl' => array_keys($objs)]);
                    }
                }
                if ($success) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                }
            } catch (Exception $exc) {
                $success = false;
                $model->addError('', $exc->getMessage());
                $transaction->rollBack();
            }
            if (!$success) {
                $details = [];
                foreach ($postDetails as $value) {
                    $detail = new SalesDtl();
                    $detail->setAttributes($value);
                    $details[] = $detail;
                }
            }
        }
        return [$details, $success];
    }

    public function actionRelease($id)
    {
        $model = $this->findModel($id);
        if ($model->status == SalesHdr::STATUS_DRAFT) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->status = SalesHdr::STATUS_RELEASE;
                if (!$model->save()) {
                    throw new UserException(implode("\n", $model->getFirstErrors()));
                }
                Yii::$app->hooks->fire(Hooks::EVENT_SALES_STDR_RELEASE_BEGIN, $model);
                foreach ($model->salesDtls as $detail) {
                Yii::$app->hooks->fire(Hooks::EVENT_SALES_STDR_RELEASE_BODY, $model,$detail);
                    
                }
                Yii::$app->hooks->fire(Hooks::EVENT_SALES_STDR_RELEASE_END, $model);
                $transaction->commit();
            } catch (Exception $exc) {
                $transaction->rollBack();
                throw new UserException($exc->getMessage());
            }
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionJs()
    {
        $p_ct = Helper::getConfigValue('sales_price', 'grosir_category', 1);
        $sql = "select p.id_product as id, p.cd_product as cd, p.nm_product as nm,
			u.id_uom, u.nm_uom, pu.isi,pc.price
			from product p
			join product_uom pu on(pu.id_product=p.id_product)
			join uom u on(u.id_uom=pu.id_uom)
			left join price pc on(pc.id_product=p.id_product and pc.id_price_category=$p_ct)
			order by p.id_product,pu.isi";
        $result = [];
        foreach (Yii::$app->db->createCommand($sql)->queryAll() as $row) {
            $id = $row['id'];
            if (!isset($result[$id])) {
                $result[$id] = [
                    'id' => $row['id'],
                    'cd' => $row['cd'],
                    'text' => $row['nm'],
                    'price' => $row['price'],
                    'id_uom' => $row['id_uom'],
                    'nm_uom' => $row['nm_uom'],
                ];
            }
            $result[$id]['uoms'][$row['id_uom']] = [
                'id' => $row['id_uom'],
                'nm' => $row['nm_uom'],
                'isi' => $row['isi']
            ];
        }
        $sql = 'select id_customer as id, nm_cust as label
			from customer';
        $cust = Yii::$app->db->createCommand($sql)->queryAll();
        return $this->renderPartial('process.js.php', ['product' => $result, 'cust' => $cust]);
    }

    /**
     * Updates an existing SalesHdr model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $payment_methods = [
            1 => 'Cash',
            2 => 'Bank',
        ];

        list($details, $success) = $this->saveSales($model);
        if ($success) {
            return $this->redirect(['view', 'id' => $model->id_sales]);
        }
        return $this->render('update', [
                'model' => $model,
                'details' => $details,
                'payment_methods' => $payment_methods]);
    }

    /**
     * Deletes an existing SalesHdr model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the SalesHdr model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SalesHdr the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SalesHdr::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}