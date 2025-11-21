<?php

namespace backend\helpers;

class TransType
{
    private static $data = [
        '3' => 'เบิก',
        '4' => 'คืนเบิก',
        '5' => 'ยืมสินค้า',
        '6' => 'คืนยืมสินค้า',
    ];

    private static $dataobj = [
        ['id'=>'3','name' => 'เบิก'],
        ['id'=>'4','name' => 'คืนเบิก'],
        ['id'=>'5','name' => 'ยืมสินค้า'],
        ['id'=>'6','name' => 'คืนยืมสินค้า'],
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
