<?php

namespace backend\helpers;

class PurchNonePrDocType
{
    private static $data = [
        '1' => 'ใบเสนอราคา / data sheet',
        '2' => 'ใบแจ้งหนี้ / ใบกำกับภาษี / ใบเสร็จ (บางที่ออกเป็นชุด)',
        '3' => 'เอกสารอื่นๆ',
    ];

    private static $dataobj = [
        ['id'=>'1','name' => 'ใบเสนอราคา / data sheet'],
        ['id'=>'2','name' => 'ใบแจ้งหนี้ / ใบกำกับภาษี / ใบเสร็จ (บางที่ออกเป็นชุด)'],
        ['id'=>'3','name' => 'เอกสารอื่นๆ'],
    ];
    public static function asArray()
    {
        return self::$data;
    }
    public static function asArrayObject()
    {
        return self::$dataobj;
    }
    public static function getTypeById($idx)
    {
        if (isset(self::$data[$idx])) {
            return self::$data[$idx];
        }

        return 'Unknown Type';
    }
    public static function getTypeByName($idx)
    {
        if (isset(self::$data[$idx])) {
            return self::$data[$idx];
        }

        return 'Unknown Type';
    }
}
