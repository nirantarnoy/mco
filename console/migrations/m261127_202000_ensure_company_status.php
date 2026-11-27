<?php

use yii\db\Migration;

/**
 * Ensures existing company records have status = 1.
 */
class m261127_202000_ensure_company_status extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('{{%company}}', ['status' => 1], ['or', ['status' => null], ['status' => 0]]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
