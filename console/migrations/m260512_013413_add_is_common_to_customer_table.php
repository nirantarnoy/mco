<?php

use yii\db\Migration;

class m260512_013413_add_is_common_to_customer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if column exists first to avoid error if manually added
        $table = Yii::$app->db->getTableSchema('{{%customer}}');
        if (!isset($table->columns['is_common'])) {
            $this->addColumn('{{%customer}}', 'is_common', $this->integer()->defaultValue(0));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%customer}}', 'is_common');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260512_013413_add_is_common_to_customer_table cannot be reverted.\n";

        return false;
    }
    */
}
