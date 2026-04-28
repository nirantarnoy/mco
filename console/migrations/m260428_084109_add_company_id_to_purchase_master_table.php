<?php

use yii\db\Migration;

class m260428_084109_add_company_id_to_purchase_master_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purchase_master}}', 'company_id', $this->integer()->comment('เว็บไซต์/บริษัท'));
        $this->createIndex(
            'idx-purchase_master-company_id',
            '{{%purchase_master}}',
            'company_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-purchase_master-company_id', '{{%purchase_master}}');
        $this->dropColumn('{{%purchase_master}}', 'company_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260428_084109_add_company_id_to_purchase_master_table cannot be reverted.\n";

        return false;
    }
    */
}
