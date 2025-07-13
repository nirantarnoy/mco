<?php

namespace backend\helpers;

class TransType
{
    private static $data = [
        '1' => 'ปรับยอดยกมา',
        '2' => 'ปรับยอด',
        '3' => 'ขาย',
        '4' => 'คืนขาย',
        '5' => 'เบิก',
        '6' => 'คืนเบิก',
        '7' => 'ยืม',
        '8' => 'คืนยืม',
    ];

    private static $dataobj = [
        ['id'=>'1','name' => 'ปรับยอดยกมา'],
        ['id'=>'2','name' => 'ปรับยอด'],
        ['id'=>'3','name' => 'ขาย'],
        ['id'=>'4','name' => 'คืนขาย'],
        ['id'=>'5','name' => 'เบิก'],
        ['id'=>'6','name' => 'คืนเบิก'],
        ['id'=>'7','name' => 'ยืม'],
        ['id'=>'8','name' => 'คืนยืม'],
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
