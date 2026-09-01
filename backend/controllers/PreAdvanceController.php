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
        $model->status = PreAdvance::STATUS_ACTIVE;

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

    public function actionGetNonePrByVendor($vendor_id = null, $q = null, $pre_advance_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $result = [];

        // Resolve vendor filter (can be vendor ID or vendor code)
        $vendorCode = null;
        $vendorIdInt = null;
        if ($vendor_id && $vendor_id !== 'null' && $vendor_id !== '') {
            if (is_numeric($vendor_id)) {
                $vModel = \backend\models\Vendor::findOne($vendor_id);
                if ($vModel) {
                    $vendorIdInt = (int)$vModel->id;
                    $vendorCode = $vModel->code;
                } else {
                    $vendorIdInt = (int)$vendor_id;
                    $vendorCode = (string)$vendor_id;
                }
            } else {
                $vModel = \backend\models\Vendor::find()->where(['code' => $vendor_id])->one();
                if ($vModel) {
                    $vendorIdInt = (int)$vModel->id;
                    $vendorCode = $vModel->code;
                } else {
                    $vendorCode = (string)$vendor_id;
                }
            }
        }

        // Fetch used references once in a single query to eliminate N+1 queries
        $usedRefsQuery = \backend\models\PreAdvanceRef::find()
            ->alias('r')
            ->select(['r.ref_type', 'r.ref_id'])
            ->innerJoin('pre_advance pa', 'r.pre_advance_id = pa.id')
            ->where(['!=', 'pa.status', \backend\models\PreAdvance::STATUS_CANCELLED]);

        if ($pre_advance_id) {
            $usedRefsQuery->andWhere(['!=', 'r.pre_advance_id', $pre_advance_id]);
        }

        $usedRefs = $usedRefsQuery->asArray()->all();
        $usedNonePrIds = [];
        $usedPoIds = [];
        foreach ($usedRefs as $ref) {
            if ($ref['ref_type'] === \backend\models\PreAdvanceRef::REF_TYPE_NONE_PR) {
                $usedNonePrIds[] = (int)$ref['ref_id'];
            } elseif ($ref['ref_type'] === \backend\models\PreAdvanceRef::REF_TYPE_PO) {
                $usedPoIds[] = (int)$ref['ref_id'];
            }
        }

        // 1. None PR (PurchaseMaster)
        $queryNonePr = \backend\models\PurchaseMaster::find()
            ->where(['approve_status' => \backend\models\PurchaseMaster::APPROVE_STATUS_APPROVED])
            ->andWhere(['status' => \backend\models\PurchaseMaster::STATUS_ACTIVE])
            ->andWhere(['>', 'total_amount', 0]);
            
        if (!empty($usedNonePrIds)) {
            $queryNonePr->andWhere(['not in', 'id', $usedNonePrIds]);
        }

        if ($vendorCode || $vendorIdInt) {
            $conds = ['or'];
            if ($vendorCode) $conds[] = ['supcod' => $vendorCode];
            if ($vendorIdInt) $conds[] = ['supcod' => $vendorIdInt];
            $queryNonePr->andWhere($conds);
        }
        
        if ($q !== null && trim($q) !== '') {
            $searchTerm = trim($q);
            $queryNonePr->andWhere([
                'or',
                ['like', 'docnum', $searchTerm],
                ['like', 'refnum', $searchTerm],
                ['like', 'job_no', $searchTerm],
                ['like', 'supnam', $searchTerm],
            ]);
        }
        
        $none_prs = $queryNonePr->orderBy(['id' => SORT_DESC])->limit(50)->all();
        
        foreach ($none_prs as $none_pr) {
            $result[] = [
                'id' => 'none_pr_' . $none_pr->id,
                'text' => '[None PR] ' . $none_pr->docnum . ' (ยอดรวม: ' . number_format($none_pr->total_amount, 2) . ( !empty($none_pr->supnam) ? ' - ' . $none_pr->supnam : '' ) . ')',
            ];
        }

        // 2. PO (Purch)
        $queryPo = \backend\models\Purch::find()
            ->where(['approve_status' => 1])
            ->andWhere(['>', 'net_amount', 0]);

        if (!empty($usedPoIds)) {
            $queryPo->andWhere(['not in', 'id', $usedPoIds]);
        }
            
        if ($vendorCode || $vendorIdInt) {
            $conds = ['or'];
            if ($vendorIdInt) $conds[] = ['vendor_id' => $vendorIdInt];
            if ($vendorCode) $conds[] = ['vendor_id' => $vendorCode];
            $queryPo->andWhere($conds);
        }
        
        if ($q !== null && trim($q) !== '') {
            $searchTerm = trim($q);
            $queryPo->andWhere([
                'or',
                ['like', 'purch_no', $searchTerm],
                ['like', 'ref_no', $searchTerm],
                ['like', 'vendor_name', $searchTerm],
            ]);
        }
        
        $pos = $queryPo->orderBy(['id' => SORT_DESC])->limit(50)->all();
        
        foreach ($pos as $po) {
            $result[] = [
                'id' => 'po_' . $po->id,
                'text' => '[PO] ' . $po->purch_no . ' (ยอดรวม: ' . number_format($po->net_amount, 2) . ( !empty($po->vendor_name) ? ' - ' . $po->vendor_name : '' ) . ')',
            ];
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
        
        foreach ($none_pr_ids as $item) {
            if (empty($item)) continue;
            
            if (is_string($item) && strpos($item, 'po_') === 0) {
                $po_id = (int)str_replace('po_', '', $item);
                $po = \backend\models\Purch::findOne($po_id);
                if ($po) {
                    $total_amount += $po->net_amount;
                    if (!$vendor_id) {
                        $vendor_id = $po->vendor_id;
                        $vendor_name = $po->vendor_name;
                    }
                    
                    $desc = 'เลขที่: ' . $po->purch_no;
                    if (!empty($po->ref_no)) {
                        $desc .= ' (อ้างอิง QT: ' . $po->ref_no . ')';
                    }
                    $lines[] = [
                        'line_date' => date('Y-m-d'),
                        'description' => $desc,
                        'amount' => $po->net_amount,
                        'remark' => $po->vendor_name,
                    ];
                }
            } else {
                $none_pr_id = (int)str_replace('none_pr_', '', (string)$item);
                $none_pr = \backend\models\PurchaseMaster::findOne($none_pr_id);
                if ($none_pr) {
                    $total_amount += $none_pr->total_amount;
                    if (!$vendor_id) {
                        $vendor_id = $none_pr->supcod;
                        $vendor_name = $none_pr->supnam;
                    }
                    
                    $desc = 'เลขที่: ' . $none_pr->docnum;
                    $jobRef = !empty($none_pr->job_no) ? $none_pr->job_no : $none_pr->refnum;
                    if (!empty($jobRef)) {
                        $desc .= ' (อ้างอิง QT: ' . $jobRef . ')';
                    }
                    $lines[] = [
                        'line_date' => date('Y-m-d'),
                        'description' => $desc,
                        'amount' => $none_pr->total_amount,
                        'remark' => $none_pr->supnam,
                    ];
                }
            }
        }
        
        return [
            'success' => true,
            'amount' => $total_amount,
            'none_pr_ids' => $none_pr_ids,
            'vendor_id' => $vendor_id ?? null,
            'vendor_name' => $vendor_name ?? null,
            'lines' => $lines,
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
        
        foreach ($none_pr_ids as $item) {
            if (empty($item)) continue;
            
            if (is_string($item) && strpos($item, 'po_') === 0) {
                $po_id = (int)str_replace('po_', '', $item);
                $po = \backend\models\Purch::findOne($po_id);
                if ($po) {
                    $isUsed = \backend\models\PreAdvanceRef::find()
                        ->where(['ref_type' => \backend\models\PreAdvanceRef::REF_TYPE_PO, 'ref_id' => $po->id])
                        ->andWhere(['!=', 'pre_advance_id', $model->id])
                        ->exists();
                    
                    if (!$isUsed) {
                        $ref = new PreAdvanceRef();
                        $ref->pre_advance_id = $model->id;
                        $ref->ref_type = PreAdvanceRef::REF_TYPE_PO;
                        $ref->ref_id = $po->id;
                        $ref->save(false);
                    }
                }
            } else {
                $none_pr_id = (int)str_replace('none_pr_', '', (string)$item);
                $none_pr = \backend\models\PurchaseMaster::findOne($none_pr_id);
                if ($none_pr) {
                    $isUsed = \backend\models\PreAdvanceRef::find()
                        ->where(['ref_type' => \backend\models\PreAdvanceRef::REF_TYPE_NONE_PR, 'ref_id' => $none_pr->id])
                        ->andWhere(['!=', 'pre_advance_id', $model->id])
                        ->exists();
                    
                    if (!$isUsed) {
                        $ref = new PreAdvanceRef();
                        $ref->pre_advance_id = $model->id;
                        $ref->ref_type = PreAdvanceRef::REF_TYPE_NONE_PR;
                        $ref->ref_id = $none_pr->id;
                        $ref->save(false);
                    }
                }
            }
        }

        $this->syncAttachmentsToSources($model);
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
        $uploadPath = Yii::getAlias('@backend/web/uploads/pre_advance/');
        if (!file_exists($uploadPath)) {
            FileHelper::createDirectory($uploadPath, 0777);
        }

        $files = UploadedFile::getInstancesByName('upload_files');
        if ($files) {
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

        // $this->syncAttachmentsToSources($model);
    }

    private function syncAttachmentsToSources($model)
    {
        $uploadPathPreAdvance = Yii::getAlias('@backend/web/uploads/pre_advance/');
        $uploadPathPurch = Yii::getAlias('@backend/web/uploads/purch_doc/');
        if (!file_exists($uploadPathPurch)) {
            FileHelper::createDirectory($uploadPathPurch, 0777);
        }

        $docs = PreAdvanceDoc::find()->where(['pre_advance_id' => $model->id])->all();

        foreach ($docs as $doc) {
            if (file_exists($uploadPathPreAdvance . $doc->file_path) && !file_exists($uploadPathPurch . $doc->file_path)) {
                @copy($uploadPathPreAdvance . $doc->file_path, $uploadPathPurch . $doc->file_path);
            }

            if ($doc->ref_type == PreAdvanceRef::REF_TYPE_PO && $doc->ref_id) {
                $exists = \common\models\PurchDoc::find()
                    ->where(['purch_id' => $doc->ref_id, 'doc_name' => $doc->file_path])
                    ->exists();
                if (!$exists) {
                    $purchDoc = new \common\models\PurchDoc();
                    $purchDoc->purch_id = $doc->ref_id;
                    $purchDoc->doc_name = $doc->file_path;
                    $purchDoc->doc_type_id = 3;
                    $purchDoc->save(false);
                }
            } elseif ($doc->ref_type == PreAdvanceRef::REF_TYPE_NONE_PR && $doc->ref_id) {
                $exists = \common\models\PurchNonePrDoc::find()
                    ->where(['purchase_master_id' => $doc->ref_id, 'doc_name' => $doc->file_path])
                    ->exists();
                if (!$exists) {
                    $nonePrDoc = new \common\models\PurchNonePrDoc();
                    $nonePrDoc->purchase_master_id = $doc->ref_id;
                    $nonePrDoc->doc_name = $doc->file_path;
                    $nonePrDoc->doc_type_id = 3;
                    $nonePrDoc->save(false);
                }
            } else {
                $refs = PreAdvanceRef::find()->where(['pre_advance_id' => $model->id])->all();
                foreach ($refs as $ref) {
                    if ($ref->ref_type == PreAdvanceRef::REF_TYPE_PO) {
                        $exists = \common\models\PurchDoc::find()
                            ->where(['purch_id' => $ref->ref_id, 'doc_name' => $doc->file_path])
                            ->exists();
                        if (!$exists) {
                            $purchDoc = new \common\models\PurchDoc();
                            $purchDoc->purch_id = $ref->ref_id;
                            $purchDoc->doc_name = $doc->file_path;
                            $purchDoc->doc_type_id = 3;
                            $purchDoc->save(false);
                        }
                    } elseif ($ref->ref_type == PreAdvanceRef::REF_TYPE_NONE_PR) {
                        $exists = \common\models\PurchNonePrDoc::find()
                            ->where(['purchase_master_id' => $ref->ref_id, 'doc_name' => $doc->file_path])
                            ->exists();
                        if (!$exists) {
                            $nonePrDoc = new \common\models\PurchNonePrDoc();
                            $nonePrDoc->purchase_master_id = $ref->ref_id;
                            $nonePrDoc->doc_name = $doc->file_path;
                            $nonePrDoc->doc_type_id = 3;
                            $nonePrDoc->save(false);
                        }
                    }
                }
            }
        }
    }

    public function actionRemoveAttachment($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $doc = PreAdvanceDoc::findOne($id);
        if ($doc) {
            $filePath = $doc->file_path;
            if ($doc->delete()) {
                \common\models\PurchDoc::deleteAll(['doc_name' => $filePath]);
                \common\models\PurchNonePrDoc::deleteAll(['doc_name' => $filePath]);
                return ['success' => true];
            }
        }
        return ['success' => false];
    }
}
