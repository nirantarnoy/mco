<?php

namespace backend\helpers;

/**
 * Address Helper
 * จัดการการแสดงผลที่อยู่ให้ถูกต้องตามรูปแบบไทย
 * - กรุงเทพฯ: แขวง / เขต / กรุงเทพมหานคร
 * - จังหวัดอื่น: ตำบล / อำเภอ / จังหวัด
 */
class AddressHelper
{
    /**
     * จัดรูปแบบที่อยู่เต็มรูปแบบ
     * @param array $addressData ข้อมูลที่อยู่
     * @return string ที่อยู่ที่จัดรูปแบบแล้ว
     */
    public static function formatFullAddress($addressData)
    {
        if (empty($addressData)) {
            return '';
        }

        $parts = [];

        // บ้านเลขที่
        if (!empty($addressData['home_number'])) {
            $parts[] = 'เลขที่ ' . $addressData['home_number'];
        }

        // ซอย
        if (!empty($addressData['aisle'])) {
            $parts[] = 'ซอย' . $addressData['aisle'];
        }

        // ถนน
        if (!empty($addressData['street'])) {
            $parts[] = 'ถนน' . $addressData['street'];
        }

        // ตรวจสอบว่าเป็นกรุงเทพฯ หรือไม่
        $isBangkok = self::isBangkok($addressData['province_name'] ?? '');

        // ตำบล/แขวง
        if (!empty($addressData['district_name'])) {
            if ($isBangkok) {
                $parts[] = 'แขวง' . $addressData['district_name'];
            } else {
                $parts[] = 'ตำบล' . $addressData['district_name'];
            }
        }

        // อำเภอ/เขต
        if (!empty($addressData['city_name'])) {
            if ($isBangkok) {
                $parts[] = 'เขต' . $addressData['city_name'];
            } else {
                $parts[] = 'อำเภอ' . $addressData['city_name'];
            }
        }

        // จังหวัด
        if (!empty($addressData['province_name'])) {
            if ($isBangkok) {
                $parts[] = 'กรุงเทพมหานคร';
            } else {
                $parts[] = 'จังหวัด' . $addressData['province_name'];
            }
        }

        // รหัสไปรษณีย์
        if (!empty($addressData['zipcode'])) {
            $parts[] = $addressData['zipcode'];
        }

        return implode(' ', $parts);
    }

    /**
     * ตรวจสอบว่าเป็นกรุงเทพฯ หรือไม่
     * @param string $province ชื่อจังหวัด
     * @return bool
     */
    public static function isBangkok($province)
    {
        if (empty($province)) {
            return false;
        }

        $bangkokNames = [
            'กรุงเทพ',
            'กรุงเทพฯ',
            'กรุงเทพมหานคร',
            'bangkok',
            'Bangkok',
            'BANGKOK',
            'กทม',
            'กทม.',
        ];

        foreach ($bangkokNames as $name) {
            if (stripos($province, $name) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * ทำความสะอาดที่อยู่ที่มีข้อมูลไม่สมบูรณ์
     * @param string $address ที่อยู่ที่ต้องการทำความสะอาด
     * @return string ที่อยู่ที่ทำความสะอาดแล้ว
     */
    public static function cleanAddress($address)
    {
        if (empty($address)) {
            return '';
        }

        // ลบคำนำหน้าที่ตามด้วย - หรือช่องว่างเกิน
        $patterns = [
            '/เลขที่\s*-\s*/',
            '/ซอย\s*-\s*/',
            '/ถนน\s*-\s*/',
            '/ตำบล\s*-\s*/',
            '/แขวง\s*-\s*/',
            '/อำเภอ\s*-\s*/',
            '/อําเภอ\s*-\s*/',
            '/เขต\s*-\s*/',
            '/จังหวัด\s*-\s*/',
            '/ตำบล\/แขวง\s*-\s*/',
            '/อำเภอ\/เขต\s*-\s*/',
            '/อําเภอ\/เขต\s*-\s*/',
            '/\s+/', // ลบช่องว่างซ้ำๆ
        ];

        $replacements = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ' ', // แทนที่ช่องว่างซ้ำๆ ด้วยช่องว่างเดียว
        ];

        $cleaned = preg_replace($patterns, $replacements, $address);

        return trim($cleaned);
    }

    /**
     * จัดรูปแบบที่อยู่จาก Customer model
     * @param \backend\models\Customer $customer
     * @return string
     */
    public static function formatCustomerAddress($customer)
    {
        if (!$customer) {
            return '';
        }

        return self::formatFullAddress([
            'home_number' => $customer->home_number,
            'aisle' => $customer->aisle,
            'street' => $customer->street,
            'district_name' => $customer->district_name,
            'city_name' => $customer->city_name,
            'province_name' => $customer->province_name,
            'zipcode' => $customer->zipcode,
        ]);
    }
}
