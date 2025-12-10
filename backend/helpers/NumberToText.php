<?php

namespace backend\helpers;

class NumberToText
{
    private static $dictionary = [
        0 => 'Zero',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen',
        20 => 'Twenty',
        30 => 'Thirty',
        40 => 'Forty',
        50 => 'Fifty',
        60 => 'Sixty',
        70 => 'Seventy',
        80 => 'Eighty',
        90 => 'Ninety',
        100 => 'Hundred',
        1000 => 'Thousand',
        1000000 => 'Million',
        1000000000 => 'Billion',
    ];

    public static function convert($number)
    {
        $number = str_replace(',', '', $number);
        // Ensure 2 decimal places fixed
        $number = number_format((float)$number, 2, '.', '');
        $parts = explode('.', $number);

        $whole = (int)$parts[0];
        $fraction = (int)$parts[1];

        if ($whole == 0) {
            $text = 'Zero Baht';
        } else {
            $text = self::convertNumber($whole) . ' Baht';
        }

        if ($fraction > 0) {
            $text .= ' and ' . self::convertNumber($fraction) . ' Satang';
        } else {
            $text .= ' Only';
        }

        return $text;
    }

    private static function convertNumber($number)
    {
        if ($number < 0) return 'Minus ' . self::convertNumber(-$number);

        $string = '';

        switch (true) {
            case $number < 21:
                $string = self::$dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int)($number / 10)) * 10;
                $units = $number % 10;
                $string = self::$dictionary[$tens];
                if ($units) {
                    $string .= ' ' . self::$dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = (int)($number / 100);
                $remainder = $number % 100;
                $string = self::$dictionary[$hundreds] . ' ' . self::$dictionary[100];
                if ($remainder) {
                    $string .= ' ' . self::convertNumber($remainder);
                }
                break;
            default:
                // Handle larger numbers (Thrive on recursion)
                $units = [1000000000 => 'Billion', 1000000 => 'Million', 1000 => 'Thousand'];
                foreach ($units as $base => $label) {
                    if ($number >= $base) {
                        $count = (int)($number / $base);
                        $remainder = $number % $base;
                        $string = self::convertNumber($count) . ' ' . $label;
                        if ($remainder) {
                            $string .= ' ' . self::convertNumber($remainder);
                        }
                        return $string;
                    }
                }
                break;
        }
        return $string;
    }
}
