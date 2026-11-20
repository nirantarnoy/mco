<?php

use yii\db\Migration;

/**
 * Class m261120_154500_drop_parent_fk_from_invoice_relations
 */
class m261120_154500_drop_parent_fk_from_invoice_relations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = $this->db->getTableSchema('invoice_relations', true);
        if ($table && isset($table->foreignKeys['fk_invoice_relations_parent'])) {
            $this->dropForeignKey('fk_invoice_relations_parent', 'invoice_relations');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Don't re-add as parent_invoice_id stores credit_note and debit_note IDs as well
    }
}

