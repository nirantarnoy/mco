<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%chart_of_account}}`.
 */
class m260303_131123_create_chart_of_account_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%chart_of_account}}', [
            'id' => $this->primaryKey(),
            'account_code' => $this->string(20)->notNull(),
            'account_name' => $this->string(255)->notNull(),
            'account_group' => $this->string(50),
            'account_level' => $this->integer()->defaultValue(1),
            'account_type' => $this->tinyInteger()->defaultValue(2)->comment('1=Control, 2=Detail'),
            'parent_account_id' => $this->integer(),
            'company_id' => $this->integer(),
            'status' => $this->tinyInteger()->defaultValue(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->createIndex('idx-chart_of_account-account_code', '{{%chart_of_account}}', ['account_code', 'company_id'], true);
        $this->addForeignKey('fk-chart_of_account-parent_account_id', '{{%chart_of_account}}', 'parent_account_id', '{{%chart_of_account}}', 'id', 'SET NULL', 'CASCADE');

        // Seed initial data
        $this->seedData();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-chart_of_account-parent_account_id', '{{%chart_of_account}}');
        $this->dropTable('{{%chart_of_account}}');
    }

    private function seedData()
    {
        $data = [
            ['1000-00', 'สินทรัพย์', 'ส/ท', 1, 1, null],
            ['1100-00', 'สินทรัพย์หมุนเวียน', 'ส/ท', 2, 1, '1000-00'],
            ['1110-00', 'เงินสดและเงินฝากธนาคาร', 'ส/ท', 3, 1, '1100-00'],
            ['1111-00', 'เงินสด', 'ส/ท', 4, 2, '1110-00'],
            ['1111-50', 'เงินสดย่อย', 'ส/ท', 4, 2, '1110-00'],
            ['1112-00', 'เงินฝากกระแสรายวัน', 'ส/ท', 4, 1, '1110-00'],
            ['1112-01', 'เงินฝากกระแสรายวัน 277-302318-5 BBL', 'ส/ท', 5, 2, '1112-00'],
            ['1112-02', 'เงินฝากกระแสรายวัน 783-364-063-5 UOB', 'ส/ท', 5, 2, '1112-00'],
            ['1113-00', 'เงินฝากออมทรัพย์', 'ส/ท', 4, 1, '1110-00'],
            ['1113-01', 'เงินฝากออมทรัพย์ 277-4-87978-3', 'ส/ท', 5, 2, '1113-00'],
            ['1113-02', 'เงินฝากออมทรัพย์ 781-1-72945-4 UOB พัทยา', 'ส/ท', 5, 2, '1113-00'],
            ['1113-03', 'เงินฝากออมทรัพย์ 783-180-804-0 UOB บ้านฉาง', 'ส/ท', 5, 2, '1113-00'],
            ['2000-00', 'หนี้สิน', 'หนี้สิน', 1, 1, null],
            ['2100-00', 'หนี้สินหมุนเวียน', 'หนี้สิน', 2, 1, '2000-00'],
            ['2120-00', 'เจ้าหนี้การค้าและตั๋วเงินจ่าย', 'หนี้สิน', 3, 1, '2100-00'],
            ['2120-01', 'เจ้าหนี้การค้า', 'หนี้สิน', 4, 2, '2120-00'],
            ['3000-00', 'ส่วนของผู้ถือหุ้น', 'ทุน', 1, 1, null],
            ['3100-00', 'ทุนเรือนหุ้น', 'ทุน', 2, 2, '3000-00'],
            ['4000-00', 'รายได้', 'รายได้', 1, 1, null],
            ['4100-00', 'รายได้จากการขายสินค้า-สุทธิ', 'รายได้', 2, 1, '4000-00'],
            ['4100-01', 'รายได้จากการขาย', 'รายได้', 3, 2, '4100-00'],
            ['5000-00', 'ค่าใช้จ่าย', 'คชจ.', 1, 1, null],
            ['5100-00', 'ต้นทุนขายสุทธิ', 'คชจ.', 2, 1, '5000-00'],
        ];

        foreach ($data as $item) {
            $parentId = null;
            if ($item[5] !== null) {
                $parent = (new \yii\db\Query())
                    ->select(['id'])
                    ->from('{{%chart_of_account}}')
                    ->where(['account_code' => $item[5]])
                    ->one();
                if ($parent) {
                    $parentId = $parent['id'];
                }
            }

            $this->insert('{{%chart_of_account}}', [
                'account_code' => $item[0],
                'account_name' => $item[1],
                'account_group' => $item[2],
                'account_level' => $item[3],
                'account_type' => $item[4],
                'parent_account_id' => $parentId,
                'status' => 1,
                'created_at' => time(),
            ]);
        }
    }
}
