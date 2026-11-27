<?php

use yii\db\Migration;

/**
 * Updates full_address for vendors that have address fields but empty full_address.
 */
class m261127_194800_populate_vendor_full_address extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Batch update vendors where full_address is null or empty
        $vendors = (new \yii\db\Query())
            ->from('{{%vendor}}')
            ->where(['or', ['full_address' => null], ['full_address' => '']])
            ->all();

        $updated = 0;
        foreach ($vendors as $v) {
            $model = new \backend\models\Vendor();
            $model->setAttributes($v, false);
            $fullAddress = \backend\helpers\AddressHelper::formatVendorAddress($model);
            if (!empty($fullAddress)) {
                $this->update('{{%vendor}}', ['full_address' => $fullAddress], ['id' => $v['id']]);
                $updated++;
            }
        }
        echo "Populated full_address for {$updated} vendors.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // No need to revert populated addresses
        return true;
    }
}
