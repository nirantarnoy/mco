<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%purch_line}}`.
 */
class m250710_143131_add_unit_id_column_to_purch_line_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%purch_line}}', 'unit_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%purch_line}}', 'unit_id');
    }
}
