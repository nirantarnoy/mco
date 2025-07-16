<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%customer}}`.
 */
class m250716_140808_add_home_number_column_to_customer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%customer}}', 'home_number', $this->string());
        $this->addColumn('{{%customer}}', 'street', $this->string());
        $this->addColumn('{{%customer}}', 'aisle', $this->string());
        $this->addColumn('{{%customer}}', 'district_name', $this->string());
        $this->addColumn('{{%customer}}', 'city_name', $this->string());
        $this->addColumn('{{%customer}}', 'province_name', $this->string());
        $this->addColumn('{{%customer}}', 'zipcode', $this->string());
        $this->addColumn('{{%customer}}', 'is_head', $this->integer());
        $this->addColumn('{{%customer}}', 'branch_name', $this->string());
        $this->addColumn('{{%customer}}', 'contact_name', $this->string());
        $this->addColumn('{{%customer}}', 'phone', $this->string());
        $this->addColumn('{{%customer}}', 'email', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%customer}}', 'home_number');
        $this->dropColumn('{{%customer}}', 'street');
        $this->dropColumn('{{%customer}}', 'aisle');
        $this->dropColumn('{{%customer}}', 'district_name');
        $this->dropColumn('{{%customer}}', 'city_name');
        $this->dropColumn('{{%customer}}', 'province_name');
        $this->dropColumn('{{%customer}}', 'zipcode');
        $this->dropColumn('{{%customer}}', 'is_head');
        $this->dropColumn('{{%customer}}', 'branch_name');
        $this->dropColumn('{{%customer}}', 'contact_name');
        $this->dropColumn('{{%customer}}', 'phone');
        $this->dropColumn('{{%customer}}', 'email');
    }
}
