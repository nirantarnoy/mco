<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_none_pr_doc}}`.
 */
class m260812_080515_add_type_description_to_purch_none_pr_doc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch_none_pr_doc}}', 'type_description', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch_none_pr_doc}}', 'type_description');
    }
}
