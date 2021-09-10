<?php


namespace App\Models;

/**
 * Class B24CustomFields
 * @package App\DomainObjects\B24
 *
 *
 * класс полагается на следующий формат отдаваемый Bitrix24API
 *
 * "UF_CRM_1509605821" => array:11 [
 * "type" => "enumeration"
 * "isRequired" => true
 * "isReadOnly" => false
 * "isImmutable" => false
 * "isMultiple" => false
 * "isDynamic" => true
 * "items" => array:30 [
 * 0 => array:2 [
 * "ID" => "231"
 * "VALUE" => "Асфальт/дорога/тротуар"
 * ]
 * 1 => array:2 [
 * "ID" => "5291"
 * "VALUE" => "Благоустройство"
 * ]
 * 2 => array:2 [
 * "ID" => "173"
 * "VALUE" => "Вентиляция"
 * ]
 * 3 => array:2 [
 * "ID" => "5293"
 * "VALUE" => "Видеонаблюдение"
 * ]
 */
class B24CustomFields
{

    /**
     * @var array
     */
    private $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;

    }


    /**
     * @throws \Exception
     */
    public function getEnumIdByValue(string $value)
    {
        if (optional($this->fields)["type"] != "enumeration")
            throw new \Exception('getEnumValueById поле - не является enum типом');

        return
            collect($this->fields["items"])
                ->where("VALUE", $value)
                ->pluck("ID")
                ->first();
    }


    public function getItems() : array
    {
        return (array)optional($this->fields)['items'];
    }

    public function getEnumIds() : array
    {
        return collect(
            $this->getItems()
        )->pluck("ID")
            ->toArray();

    }
    public function getEnumIdsByValues($values) : array
    {
        $values = is_array($values)
            ? $values
            : explode(",", $values);

        return collect(
            $this->getItems()
        )->whereIn("VALUE", $values)
            ->pluck("ID")
            ->whenEmpty(function($collection){ return $collection->push("") ; }) // [""] для очистки поля
            ->toArray() ;

    }

    public function isMultiple()  : bool
    {
        return (bool)optional($this->fields)["isMultiple"];

    }
}
