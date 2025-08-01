<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%credit_notes}}`.
 */
class m250801_061613_create_credit_notes_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = "
               CREATE TABLE `debit_note` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `document_no` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
      `document_date` date NOT NULL,
      `customer_id` int(11) NOT NULL,
      `invoice_id` int(11) DEFAULT NULL,
      `original_invoice_no` varchar(20) COLLATE utf8_unicode_ci,
      `original_invoice_date` date,
      `original_amount` decimal(15,2) DEFAULT '0.00',
      `adjust_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
      `vat_percent` decimal(5,2) DEFAULT '7.00',
      `vat_amount` decimal(15,2) DEFAULT '0.00',
      `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
      `reason` text COLLATE utf8_unicode_ci NOT NULL,
      `amount_text` varchar(255) COLLATE utf8_unicode_ci,
      `status` enum('draft','approved','cancelled') COLLATE utf8_unicode_ci DEFAULT 'draft',
      `approved_by` int(11) DEFAULT NULL,
      `approved_date` datetime DEFAULT NULL,
      `created_at` datetime DEFAULT NULL,
      `updated_at` datetime DEFAULT NULL,
      `created_by` int(11) DEFAULT NULL,
      `updated_by` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `document_no` (`document_no`),
      KEY `fk_debit_customer` (`customer_id`),
      KEY `fk_debit_invoice` (`invoice_id`),
      KEY `idx_document_date` (`document_date`),
      CONSTRAINT `fk_debit_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
      CONSTRAINT `fk_debit_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        \Yii::$app->db->createCommand($sql)->execute();

        $sql = "
            CREATE TABLE `debit_note_item` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `debit_note_id` int(11) NOT NULL,
          `item_no` int(11) NOT NULL,
          `description` text COLLATE utf8_unicode_ci NOT NULL,
          `quantity` decimal(15,2) NOT NULL DEFAULT '1.00',
          `unit` varchar(50) COLLATE utf8_unicode_ci,
          `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
          `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
          PRIMARY KEY (`id`),
          KEY `fk_debit_note_item` (`debit_note_id`),
          CONSTRAINT `fk_debit_note_item` FOREIGN KEY (`debit_note_id`) REFERENCES `debit_note` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        \Yii::$app->db->createCommand($sql)->execute();

        $sql = "
            CREATE TABLE `credit_note` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `document_no` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
          `document_date` date NOT NULL,
          `customer_id` int(11) NOT NULL,
          `invoice_id` int(11) DEFAULT NULL,
          `original_invoice_no` varchar(20) COLLATE utf8_unicode_ci,
          `original_invoice_date` date,
          `original_amount` decimal(15,2) DEFAULT '0.00',
          `actual_amount` decimal(15,2) DEFAULT '0.00',
          `adjust_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
          `vat_percent` decimal(5,2) DEFAULT '7.00',
          `vat_amount` decimal(15,2) DEFAULT '0.00',
          `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
          `reason` text COLLATE utf8_unicode_ci NOT NULL,
          `amount_text` varchar(255) COLLATE utf8_unicode_ci,
          `status` enum('draft','approved','cancelled') COLLATE utf8_unicode_ci DEFAULT 'draft',
          `approved_by` int(11) DEFAULT NULL,
          `approved_date` datetime DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          `updated_at` datetime DEFAULT NULL,
          `created_by` int(11) DEFAULT NULL,
          `updated_by` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `document_no` (`document_no`),
          KEY `fk_credit_customer` (`customer_id`),
          KEY `fk_credit_invoice` (`invoice_id`),
          KEY `idx_document_date` (`document_date`),
          CONSTRAINT `fk_credit_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
          CONSTRAINT `fk_credit_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        \Yii::$app->db->createCommand($sql)->execute();

        $sql = "CREATE TABLE `credit_note_item` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `credit_note_id` int(11) NOT NULL,
          `item_no` int(11) NOT NULL,
          `description` text COLLATE utf8_unicode_ci NOT NULL,
          `quantity` decimal(15,2) NOT NULL DEFAULT '1.00',
          `unit` varchar(50) COLLATE utf8_unicode_ci,
          `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
          `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
          `discount_amount` decimal(15,2) DEFAULT '0.00',
          PRIMARY KEY (`id`),
          KEY `fk_credit_note_item` (`credit_note_id`),
          CONSTRAINT `fk_credit_note_item` FOREIGN KEY (`credit_note_id`) REFERENCES `credit_note` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        \Yii::$app->db->createCommand($sql)->execute();

        $sql = "
        CREATE TABLE `document_sequence` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `document_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
          `prefix` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
          `year_format` varchar(10) COLLATE utf8_unicode_ci DEFAULT 'YY',
          `running_length` int(11) DEFAULT '6',
          `last_number` int(11) DEFAULT '0',
          `last_year` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `document_type` (`document_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        \Yii::$app->db->createCommand($sql)->execute();

        $sql = "
            INSERT INTO `document_sequence` (`document_type`, `prefix`, `year_format`, `running_length`, `last_number`) VALUES
            ('invoice', 'INV', 'YY', 6, 0),
            ('debit_note', 'DBN', 'YY', 6, 0),
            ('credit_note', 'CRN', 'YY', 6, 0)";

        \Yii::$app->db->createCommand($sql)->execute();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%credit_notes}}');
    }
}
