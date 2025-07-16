<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%vendor}}`.
 */
class m250716_140833_add_home_number_column_to_vendor_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%vendor}}', 'home_number', $this->string());
        $this->addColumn('{{%vendor}}', 'street', $this->string());
        $this->addColumn('{{%vendor}}', 'aisle', $this->string());
        $this->addColumn('{{%vendor}}', 'district_name', $this->string());
        $this->addColumn('{{%vendor}}', 'city_name', $this->string());
        $this->addColumn('{{%vendor}}', 'province_name', $this->string());
        $this->addColumn('{{%vendor}}', 'zipcode', $this->string());
        $this->addColumn('{{%vendor}}', 'is_head', $this->integer());
        $this->addColumn('{{%vendor}}', 'branch_name', $this->string());
        $this->addColumn('{{%vendor}}', 'contact_name', $this->string());
        $this->addColumn('{{%vendor}}', 'phone', $this->string());
        $this->addColumn('{{%vendor}}', 'email', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%vendor}}', 'home_number');
        $this->dropColumn('{{%vendor}}', 'street');
        $this->dropColumn('{{%vendor}}', 'aisle');
        $this->dropColumn('{{%vendor}}', 'district_name');
        $this->dropColumn('{{%vendor}}', 'city_name');
        $this->dropColumn('{{%vendor}}', 'province_name');
        $this->dropColumn('{{%vendor}}', 'zipcode');
        $this->dropColumn('{{%vendor}}', 'is_head');
        $this->dropColumn('{{%vendor}}', 'branch_name');
        $this->dropColumn('{{%vendor}}', 'contact_name');
        $this->dropColumn('{{%vendor}}', 'phone');
        $this->dropColumn('{{%vendor}}', 'email');
    }
}
