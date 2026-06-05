<?php

namespace backend\controllers;

use Yii;
use backend\models\PreAdvance;
use backend\models\PreAdvanceLine;
use backend\models\PreAdvanceDoc;
use backend\models\PreAdvanceRef;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

/**
 * PreAdvanceController implements the CRUD actions for PreAdvance model.
 */
class PreAdvanceController extends BaseController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        // For simplicity, we can use an ActiveDataProvider here or create a Search model
        $query = PreAdvance::find()->orderBy(['id' => SORT_DESC]);
        
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new PreAdvance();
        $model->trans_date = date('Y-m-d');
        $model->status = PreAdvance::STATUS_DRAFT;

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $this->savePreAdvanceLines($model);
                    $this->savePreAdvanceRefs($model);
                    $this->uploadAttachments($model);

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'บันทึก Pre-Advance สำเร็จ');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $this->savePreAdvanceLines($model);
                    $this->savePreAdvanceRefs($model);
                    $this->uploadAttachments($model);

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'อัปเดต Pre-Advance สำเร็จ');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    public function actionGetNonePrByVendor($vendor_id = null, $q = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $query = \backend\models\PurchaseMaster::find()
            ->where(['approve_status' => \backend\models\PurchaseMaster::APPROVE_STATUS_APPROVED])
            ->andWhere(['status' => \backend\models\PurchaseMaster::STATUS_ACTIVE])
            ->andWhere(['>', 'total_amount', 0]);
            
        if ($vendor_id && $vendor_id !== 'null' && $vendor_id !== '') {
            $query->andWhere(['supcod' => $vendor_id]);
        }
        
        if ($q) {
            $query->andWhere(['like', 'docnum', $q]);
        }
        
        $none_prs = $query->limit(20)->all();
        
        $result = [];
        foreach ($none_prs as $none_pr) {
            $paidAmount = \backend\models\PreAdvanceRef::find()
                ->where(['ref_type' => \backend\models\PreAdvanceRef::REF_TYPE_NONE_PR, 'ref_id' => $none_pr->id])
                ->sum('amount') ?: 0;
            
            $remaining = $none_pr->total_amount - $paidAmount;
            
            if ($remaining > 0) {
                $result[] = [
                    'id' => $none_pr->id,
                    'text' => $none_pr->docnum . ' (คงเหลือ: ' . number_format($remaining, 2) . ( !empty($none_pr->supnam) ? ' - ' . $none_pr->supnam : '' ) . ')',
                ];
            }
        }
        
        return ['results' => $result];
    }

    public function actionPullMultiple()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $none_pr_ids = Yii::$app->request->post('none_pr_ids', []);
        
        $lines = [];
        $total_amount = 0;
        $vendor_id = null;
        $vendor_name = null;
        
        foreach ($none_pr_ids as $none_pr_id) {
            $none_pr = \backend\models\PurchaseMaster::findOne($none_pr_id);
            if ($none_pr) {
                $paidAmount = \backend\models\PreAdvanceRef::find()
                    ->where(['ref_type' => \backend\models\PreAdvanceRef::REF_TYPE_NONE_PR, 'ref_id' => $none_pr->id])
                    ->sum('amount') ?: 0;
                
                $remaining = $none_pr->total_amount - $paidAmount;
                $total_amount += $remaining;
                if (!$vendor_id) {
                    $vendor_id = $none_pr->supcod;
                    $vendor_name = $none_pr->supnam;
                }
            }
        }
        
        return [
            'success' => true,
            'amount' => $total_amount,
            'none_pr_ids' => $none_pr_ids,
            'vendor_id' => $vendor_id ?? null,
            'vendor_name' => $vendor_name ?? null,
        ];
    }

    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $this->layout = false;
        return $this->render('print', [
            'model' => $model,
        ]);
    }

    private function savePreAdvanceLines($model)
    {
        PreAdvanceLine::deleteAll(['pre_advance_id' => $model->id]);
        
        $dates = Yii::$app->request->post('line_date', []);
        $descriptions = Yii::$app->request->post('line_description', []);
        $amounts = Yii::$app->request->post('line_amount', []);
        $remarks = Yii::$app->request->post('line_remark', []);
        
        foreach ($descriptions as $i => $desc) {
            if (empty($desc) && empty($amounts[$i])) continue;
            
            $line = new PreAdvanceLine();
            $line->pre_advance_id = $model->id;
            $line->line_date = $dates[$i] ?? null;
            $line->description = $desc;
            $line->amount = $amounts[$i] ?? 0;
            $line->remark = $remarks[$i] ?? '';
            $line->save(false);
        }
    }

    private function savePreAdvanceRefs($model)
    {
        $none_pr_ids_raw = Yii::$app->request->post('none_pr_ids', []);
        
        if (is_string($none_pr_ids_raw)) {
            $none_pr_ids = json_decode($none_pr_ids_raw, true) ?: [];
        } else {
            $none_pr_ids = $none_pr_ids_raw;
        }
        
        PreAdvanceRef::deleteAll(['pre_advance_id' => $model->id]);
        
        foreach ($none_pr_ids as $none_pr_id) {
            $none_pr = \backend\models\PurchaseMaster::findOne($none_pr_id);
            if ($none_pr) {
                $paidAmount = \backend\models\PreAdvanceRef::find()
                    ->where(['ref_type' => \backend\models\PreAdvanceRef::REF_TYPE_NONE_PR, 'ref_id' => $none_pr->id])
                    ->andWhere(['!=', 'pre_advance_id', $model->id])
                    ->sum('amount') ?: 0;
                
                $remaining = $none_pr->total_amount - $paidAmount;
                
                if ($remaining > 0) {
                    $ref = new PreAdvanceRef();
                    $ref->pre_advance_id = $model->id;
                    $ref->ref_type = PreAdvanceRef::REF_TYPE_NONE_PR;
                    $ref->ref_id = $none_pr->id;
                    $ref->save(false);
                }
            }
        }
    }

    protected function findModel($id)
    {
        if (($model = PreAdvance::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    private function uploadAttachments($model)
    {
        $files = UploadedFile::getInstancesByName('upload_files');
        if ($files) {
            $uploadPath = Yii::getAlias('@backend/web/uploads/pre_advance/');
            if (!file_exists($uploadPath)) {
                FileHelper::createDirectory($uploadPath, 0777);
            }

            foreach ($files as $file) {
                $newName = time() . '_' . Yii::$app->security->generateRandomString(10) . '.' . $file->extension;
                if ($file->saveAs($uploadPath . $newName)) {
                    $doc = new PreAdvanceDoc();
                    $doc->pre_advance_id = $model->id;
                    $doc->file_name = $file->baseName . '.' . $file->extension;
                    $doc->file_path = $newName;
                    $doc->file_size = $file->size;
                    $doc->uploaded_at = time();
                    $doc->uploaded_by = Yii::$app->user->id;
                    $doc->save(false);
                }
            }
        }
    }

    public function actionRemoveAttachment($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $doc = PreAdvanceDoc::findOne($id);
        if ($doc && $doc->delete()) {
            return ['success' => true];
        }
        return ['success' => false];
    }
}
