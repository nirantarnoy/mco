<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%system_setting}}`.
 */
class m260916_120800_create_system_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%system_setting}}', [
            'id' => $this->primaryKey(),
            'setting_key' => $this->string(100)->notNull()->unique(),
            'setting_value' => $this->text(),
            'description' => $this->string(255),
            'updated_at' => $this->integer(),
        ]);

        // Insert default values for petty cash
        $this->insert('{{%system_setting}}', [
            'setting_key' => 'petty_cash_max_amount',
            'setting_value' => '30000',
            'description' => 'วงเงินสดย่อยสูงสุด',
            'updated_at' => time(),
        ]);

        $this->insert('{{%system_setting}}', [
            'setting_key' => 'petty_cash_min_amount',
            'setting_value' => '3000',
            'description' => 'วงเงินสดย่อยขั้นต่ำ',
            'updated_at' => time(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%system_setting}}');
    }
}
