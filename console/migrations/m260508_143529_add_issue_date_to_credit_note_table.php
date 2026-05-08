<?php

use yii\db\Migration;

class m260508_143529_add_issue_date_to_credit_note_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%credit_note}}', 'issue_date', $this->date()->after('document_date'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%credit_note}}', 'issue_date');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260508_143529_add_issue_date_to_credit_note_table cannot be reverted.\n";

        return false;
    }
    */
}
