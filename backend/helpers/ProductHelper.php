<?php

namespace backend\helpers;

class ProductHelper
{
    /**
     * Clean product description by removing codes and common prefixes
     * @param string $description
     * @return string
     */
    public static function cleanDescription($description)
    {
        if (empty($description)) return '';

        // 1. Check if the first word is a product code
        $firstSpace = strpos($description, ' ');
        if ($firstSpace !== false) {
            $prefix = trim(substr($description, 0, $firstSpace));
            // Check if the part before space is a valid product code in DB
            if (\backend\models\Product::find()->where(['code' => $prefix])->exists()) {
                $description = substr($description, $firstSpace + 1);
            }
        }

        // 2. Remove common prefixes and suffixes (case-insensitive)
        $description = str_ireplace('(service)', '', $description);
        $description = str_ireplace('Service -', '', $description);
        $description = str_ireplace('Service-', '', $description);
        $description = str_ireplace('Service', '', $description);
        $description = str_ireplace('Serice -', '', $description);
        $description = str_ireplace('Serice-', '', $description);
        $description = str_ireplace('Serice', '', $description);

        // 3. Remove leading hyphen if it exists
        $description = trim($description);
        if (strpos($description, '-') === 0) {
            $description = substr($description, 1);
        }

        return trim($description);
    }
}
