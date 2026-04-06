<?php

use yii\db\Migration;

/**
 * Class m260406_131008_update_refnum_in_purchase_master_table
 */
class m260406_131008_update_refnum_in_purchase_master_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%purchase_master}}', 'refnum', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%purchase_master}}', 'refnum', $this->string(50));
    }
}
