<?php


namespace App\Helpers;


class ExcelHelper
{

    public static function getNameFromNumber($num)
    {
        $numeric = ($num - 1) % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval(($num - 1) / 26);
        if ($num2 > 0) {
            return self::getNameFromNumber($num2) . $letter;
        } else {
            return $letter;
        }
    }

}
