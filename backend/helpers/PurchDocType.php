<?php

namespace backend\helpers;

class PurchDocType
{
    private static $data = [
        '1' => 'Po Acknowledge',
        '2' => 'ใบกำกับภาษี',
        '3' => 'เอกสารจ่ายเงิน',
        '4' => 'ใบแจ้งหนี้ / ใบวางบิล',
        '5' => 'ใบเสร็จรับเงิน',
    ];

    private static $dataobj = [
        ['id'=>'1','name' => 'Po Acknowledge'],
        ['id'=>'2','name' => 'ใบกำกับภาษี'],
        ['id'=>'3','name' => 'เอกสารจ่ายเงิน'],
        ['id'=>'4','name' => 'ใบแจ้งหนี้ / ใบวางบิล'],
        ['id'=>'5','name' => 'ใบเสร็จรับเงิน'],
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
