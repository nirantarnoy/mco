<?php

namespace backend\helpers;

class PurchDocType
{
    private static $data = [
        '1' => 'Po Acknowledge',
        '2' => 'ใบกำกับภาษี',
        '3' => 'เอกสารจ่ายเงิน',
    ];

    private static $dataobj = [
        ['id'=>'1','name' => 'Po Acknowledge'],
        ['id'=>'2','name' => 'ใบกำกับภาษี'],
        ['id'=>'3','name' => 'เอกสารจ่ายเงิน'],
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
