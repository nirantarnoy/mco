<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_req}}`.
 */
class m250801_012417_add_required_date_column_to_purch_req_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch_req}}', 'required_date', $this->datetime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch_req}}', 'required_date');
    }
}
