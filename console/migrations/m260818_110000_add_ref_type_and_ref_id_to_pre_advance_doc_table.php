<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%pre_advance_doc}}`.
 */
class m260818_110000_add_ref_type_and_ref_id_to_pre_advance_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $schema = $this->db->getTableSchema('{{%pre_advance_doc}}');
        if ($schema && !isset($schema->columns['ref_type'])) {
            $this->addColumn('{{%pre_advance_doc}}', 'ref_type', $this->integer()->null()->after('pre_advance_id'));
        }
        if ($schema && !isset($schema->columns['ref_id'])) {
            $this->addColumn('{{%pre_advance_doc}}', 'ref_id', $this->integer()->null()->after('ref_type'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $schema = $this->db->getTableSchema('{{%pre_advance_doc}}');
        if ($schema && isset($schema->columns['ref_id'])) {
            $this->dropColumn('{{%pre_advance_doc}}', 'ref_id');
        }
        if ($schema && isset($schema->columns['ref_type'])) {
            $this->dropColumn('{{%pre_advance_doc}}', 'ref_type');
        }
    }
}
