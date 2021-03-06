<?php

namespace biz\inventory\controllers;

use Yii;
use biz\models\TransferNotice;
use biz\models\searchs\TransferNotice as TransferNoticeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\base\Model;
use biz\tools\Hooks;
use yii\base\UserException;
use biz\base\Event;

/**
 * NoticeController implements the CRUD actions for TransferNotice model.
 */
class NoticeController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'approve' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all TransferNotice models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TransferNoticeSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single TransferNotice model.
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
     * Creates a new TransferNotice model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TransferNotice;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id_transfer]);
        } else {
            return $this->render('create', [
                    'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing TransferNotice model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $details = $model->getTransferNoticeDtls()->with('transferDtl')->indexBy('id_product')->all();
        if ($post = Yii::$app->request->post()) {
            try {
                $transaction = Yii::$app->db->beginTransaction();
                $model->status = TransferNotice::STATUS_UPDATE;
                if ($model->save() && Model::loadMultiple($details, $post) && Model::validateMultiple($details)) {
                    foreach ($details as $detail) {
                        $detail->save();
                    }
                    $transaction->commit();
                    return $this->redirect(['view', 'id' => $model->id_transfer]);
                }
                $transaction->rollBack();
            } catch (\Exception $exc) {
                $model->addError('', $exc->getMessage());
                $transaction->rollBack();
            }
        }
        return $this->render('update', [
                'model' => $model,
                'details' => $details
        ]);
    }

    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        Yii::$app->trigger(Hooks::E_INAPP_1, new Event([$model]));
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->status = TransferNotice::STATUS_APPROVE;
            if (!$model->save()) {
                throw new UserException(implode(",\n", $model->firstErrors));
            }
            Yii::$app->trigger(Hooks::E_INAPP_21, new Event([$model]));
            foreach ($model->transferNoticeDtls as $detail) {
                Yii::$app->trigger(Hooks::E_INAPP_22, new Event([$model, $detail]));
            }
            Yii::$app->trigger(Hooks::E_INAPP_23, new Event([$model]));
            $transaction->commit();
        } catch (Exception $exc) {
            $transaction->rollBack();
            throw new UserException($exc->getMessage());
        }
        return $this->redirect(['index']);
    }

    /**
     * Deletes an existing TransferNotice model.
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
     * Finds the TransferNotice model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TransferNotice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TransferNotice::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}