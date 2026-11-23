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
     * เรียงลำดับ: เลขที่, หมู่(ถ้ามี), ซอย, ถนน, แขวง/ตำบล, เขต/อำเภอ, จังหวัด, รหัสไปรษณีย์
     * (กรุงเทพฯ: แขวง / เขต / กรุงเทพมหานคร, ต่างจังหวัด: ตำบล / อำเภอ / จังหวัด)
     * @param array $addressData ข้อมูลที่อยู่
     * @return string ที่อยู่ที่จัดรูปแบบแล้ว
     */
    public static function formatFullAddress($addressData)
    {
        if (empty($addressData)) {
            return '';
        }

        $parts = [];

        // 1. บ้านเลขที่ (เลขที่)
        $homeNumber = trim($addressData['home_number'] ?? '');
        if (!empty($homeNumber) && $homeNumber !== '-') {
            $parts[] = $homeNumber;
        }

        // 2. หมู่ (ถ้ามี และยังไม่มีใน home_number)
        $moo = trim($addressData['moo'] ?? ($addressData['village_no'] ?? ''));
        if (!empty($moo) && $moo !== '-') {
            if (!preg_match('/(หมู่|ม\.)/u', $homeNumber)) {
                if (preg_match('/^(หมู่|ม\.)/u', $moo)) {
                    $parts[] = $moo;
                } else {
                    $parts[] = 'หมู่ ' . $moo;
                }
            }
        }

        // 3. ซอย
        $aisle = trim($addressData['aisle'] ?? '');
        if (!empty($aisle) && $aisle !== '-') {
            $aisle = preg_replace('/\s+/', ' ', $aisle);
            if (preg_match('/^(ซอย|ซ\.)/u', $aisle) || mb_strpos($aisle, 'ซอย') !== false) {
                $parts[] = $aisle;
            } else {
                $parts[] = 'ซอย' . $aisle;
            }
        }

        // 4. ถนน
        $street = trim($addressData['street'] ?? '');
        if (!empty($street) && $street !== '-') {
            $street = preg_replace('/\s+/', ' ', $street);
            if (preg_match('/^(ถนน|ถ\.)/u', $street)) {
                $parts[] = $street;
            } else {
                $parts[] = 'ถนน' . $street;
            }
        }

        // ตรวจสอบว่าเป็นกรุงเทพฯ หรือไม่
        $province = trim($addressData['province_name'] ?? '');
        $city = trim($addressData['city_name'] ?? '');
        $district = trim($addressData['district_name'] ?? '');
        $isBangkok = self::isBangkok($province) || preg_match('/^เขต/u', $city) || preg_match('/^แขวง/u', $district);

        // 5. แขวง (กทม.) / ตำบล (ต่างจังหวัด)
        if (!empty($district) && $district !== '-') {
            $district = preg_replace('/\s+/', ' ', $district);
            if ($isBangkok) {
                $cleanDistrict = preg_replace('/^(ตำบล|ต\.)\s*/u', '', $district);
                if (preg_match('/^แขวง/u', $cleanDistrict)) {
                    $parts[] = $cleanDistrict;
                } else {
                    $parts[] = 'แขวง' . $cleanDistrict;
                }
            } else {
                $cleanDistrict = preg_replace('/^แขวง\s*/u', '', $district);
                if (preg_match('/^(ตำบล|ต\.)/u', $cleanDistrict)) {
                    $parts[] = $cleanDistrict;
                } else {
                    $parts[] = 'ตำบล' . $cleanDistrict;
                }
            }
        }

        // 6. เขต (กทม.) / อำเภอ (ต่างจังหวัด)
        if (!empty($city) && $city !== '-') {
            $city = preg_replace('/\s+/', ' ', $city);
            if ($isBangkok) {
                $cleanCity = preg_replace('/^(อำเภอ|อ\.)\s*/u', '', $city);
                if (preg_match('/^เขต/u', $cleanCity)) {
                    $parts[] = $cleanCity;
                } else {
                    $parts[] = 'เขต' . $cleanCity;
                }
            } else {
                $cleanCity = preg_replace('/^เขต\s*/u', '', $city);
                if (preg_match('/^(อำเภอ|อ\.)/u', $cleanCity)) {
                    $parts[] = $cleanCity;
                } else {
                    $parts[] = 'อำเภอ' . $cleanCity;
                }
            }
        }

        // 7. จังหวัด
        if (!empty($province) && $province !== '-') {
            if ($isBangkok) {
                $parts[] = 'กรุงเทพมหานคร';
            } else {
                if (preg_match('/^(จังหวัด|จ\.)/u', $province)) {
                    $parts[] = $province;
                } else {
                    $parts[] = 'จังหวัด' . $province;
                }
            }
        }

        // 8. รหัสไปรษณีย์
        $zipcode = trim($addressData['zipcode'] ?? '');
        if (!empty($zipcode) && $zipcode !== '-') {
            $parts[] = $zipcode;
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
            'home_number' => $customer->home_number ?? '',
            'aisle' => $customer->aisle ?? '',
            'street' => $customer->street ?? '',
            'district_name' => $customer->district_name ?? '',
            'city_name' => $customer->city_name ?? '',
            'province_name' => $customer->province_name ?? '',
            'zipcode' => $customer->zipcode ?? '',
        ]);
    }

    /**
     * จัดรูปแบบที่อยู่จาก Vendor model
     * @param \backend\models\Vendor|\common\models\Vendor $vendor
     * @return string
     */
    public static function formatVendorAddress($vendor)
    {
        if (!$vendor) {
            return '';
        }

        return self::formatFullAddress([
            'home_number' => $vendor->home_number ?? '',
            'aisle' => $vendor->aisle ?? '',
            'street' => $vendor->street ?? '',
            'district_name' => $vendor->district_name ?? '',
            'city_name' => $vendor->city_name ?? '',
            'province_name' => $vendor->province_name ?? '',
            'zipcode' => $vendor->zipcode ?? '',
        ]);
    }
}
