<?php


namespace App\Imports;


class FieldsMapper
{

    static function map(&$fields) {

        foreach($fields as $key=>$val) {
            $fields[$key] = self::mapOne($val);
        }



    }

    static function mapOne($val) {
        switch($val) {
            case "UTM Source":
                return "Рекламная система";
            case "UTM Medium":
                return "Тип трафика";
            case "UTM Campaign":
                return "Обозначение рекламной кампании";
            case "UTM Content":
                return "Содержание кампании";
            case "UTM Term":
                return "Условие поиска кампании";
                break;

            default: return $val;
        }
    }

}
