<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purchase_detail}}`.
 */
class m251112_143327_create_purchase_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%purchase_detail}}', [
            'id' => $this->primaryKey(),
            'purchase_master_id' => $this->integer()->notNull()->comment('รหัสใบซื้อ'),
            'line_no' => $this->integer()->notNull()->comment('ลำดับที่'),
            'stkcod' => $this->string(50)->comment('รหัสสินค้า'),
            'stkdes' => $this->string(255)->comment('รายละเอียดสินค้า'),
            'uqnty' => $this->decimal(15, 3)->defaultValue(0)->comment('จำนวน'),
            'unitpr' => $this->decimal(15, 2)->defaultValue(0)->comment('ราคา/หน่วย'),
            'amount' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงิน'),
            'disc' => $this->string(50)->comment('ส่วนลด'),
            'remark' => $this->text()->comment('หมายเหตุ'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // เพิ่ม foreign key
        $this->addForeignKey(
            'fk-purchase_detail-purchase_master_id',
            '{{%purchase_detail}}',
            'purchase_master_id',
            '{{%purchase_master}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // สร้าง index
        $this->createIndex(
            'idx-purchase_detail-purchase_master_id',
            '{{%purchase_detail}}',
            'purchase_master_id'
        );

        $this->createIndex(
            'idx-purchase_detail-stkcod',
            '{{%purchase_detail}}',
            'stkcod'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-purchase_detail-purchase_master_id',
            '{{%purchase_detail}}'
        );

        $this->dropTable('{{%purchase_detail}}');
    }
}
