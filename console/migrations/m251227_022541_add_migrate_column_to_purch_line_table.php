<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_line}}`.
 */
class m251227_022541_add_migrate_column_to_purch_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch_line}}', 'migrate', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch_line}}', 'migrate');
    }
}
