<?php


namespace App\Services;


use App\Exceptions\Validate2LevelException;
use App\Models\B24CustomFields;
use App\Models\B24FieldsDictionary;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class Validate2Level
 * @package App\Services
 */
class Validate2Level
{

    /**
     * @var Collection
     */
    private $data;

    /**
     * @var Collection
     */
    private $config;
    /**
     * @var array
     */
    private $b24Entity;
    /**
     * @var int
     */
    private $b24ID;
    /**
     * @var B24CustomFields
     */
    private  $b24CustomField;

    public function __construct(array $data)
    {
        $this->data = collect($data);
        $this->b24ID = (int)$data["ID"];

    }

    /**
     * @param array $b24Entity
     * @return  Collection
     * @throws Validate2LevelException
     */
    public function validateData(array $b24Entity, Collection $fields_config): Collection
    {
        $this->b24Entity = $b24Entity;
        Log::channel('ext_debug')->debug("start validating. ");

        $this->data->except(['ID'])->each(function ($value, $key) use ($fields_config) {


            $index = $this->data->keys()->search($key) + 1;
            $this->config = $fields_config->where('title', $key)->first();

            $this->b24CustomField = new B24CustomFields(
                json_decode($this->config->items, true)
            );

            if ($this->b24CustomField->isMultiple()) {
                $value = array_map("trim", explode(",", $value));
            } else {
                $value = trim($value);

            }

            //пустое значение для поля, которое должно быть обязательным к заполнению;
            if (optional($this->config)->required && empty($value)
                && !$this->isNotAnException($this->config)) {
                $this->throwCustomError($index, $this->config->title . ' - обязательное поле');
            }

            unset($this->data[$key]); // убираем лишнее, в б24 отправятся только служебн. ключи типа UF_CRM_.... (это дейстиве в принципе не обязательно)

            if (empty($value))    {
              //  $this->data->forget($key);
               // Log::channel('ext_debug')->debug("skip empty column:", [$value, $key]);
                Log::channel('ext_debug')->debug("empty value, NO validation :", [$value, $key]);
                $this->data[$this->config->field_code] = $value;

            } else {
                $this->__validate($value, $key, $index);
            }

        });

        Log::channel('ext_debug')->debug("validated data:", $this->data->toArray());

        return $this->data;


    }

    private function isNotAnException(B24FieldsDictionary $dict): bool
    {
        if ($dict->entity_id == 3 && ($dict->title == 'Фамилия' || $dict->title == 'Отчество'))
            return true; // Фамилия , Отчество для Контакта

        return false;
    }

    private function validateNumeric($value): bool
    {
        if (is_array($value)) {
            foreach ($value as $val) {
                if (!$this->validateNumeric($val)) return false;
            }
            return true;
        }
        return is_numeric($value);
    }

    /**
     * @throws Validate2LevelException
     */
    private function throwTypeError($col_ix, $field_name, $field_type)
    {
        throw new Validate2LevelException(
            sprintf('Номер столбца: %s | ID сущности: %s | Описание ошибки: Поле  %s не соответствует типу %s',
                $col_ix, $this->b24ID, $field_name, $field_type)
        );


    }

    /**
     * @throws Validate2LevelException
     */
    private function throwCustomError($col_ix, $msg)
    {
        throw new Validate2LevelException(
            sprintf('Номер столбца: %s | ID сущности: %s | Описание ошибки: %s'  ,
                $col_ix, $this->b24ID, $msg)
        );


    }


    /**
     * @throws Validate2LevelException
     */
    public function validateID()
    {
        if (!is_numeric($this->b24ID)) {
            $index = $this->data->keys()->search("ID") + 1;
            throw new Validate2LevelException("Номер столбца:" . (string)$index . "| ID сущности:" . $this->b24ID . "| Описание ошибки:" . 'Поле ID не соответствует типу integer');
        }
    }

    /**
     * @throws Validate2LevelException
     */
    private function __validate($value, $key, $index)
    {
        //несоответствие типов - содержимое ячейки не соответствует по типу полю сущности, с которым она ассоциирована;
        $field_type = optional($this->config)->field_type;
        switch ($field_type) {
            case 'integer' :
            case 'double' :
                if (!$this->validateNumeric($value)) {
                    $this->throwTypeError($index, $key, $this->config->field_type);
                }
                $this->data[$this->config->field_code] = $value;
                break;
            case 'string' :
                $this->data[$this->config->field_code] = $value;
                break;
            case 'boolean' :
                // if (!is_bool($value))
                if ($value != "Нет" && $value != "Да" && $value) {
                    $this->throwTypeError($index, $key, $this->config->field_type);
                }
                $this->data[$this->config->field_code] = $value == "Да" ? 1 : 0;
                break;
            case 'datetime' :
            case 'date' :
                if (!$this->validateNumeric($value)) { // даты приходят числами из импорта
                    $this->throwTypeError($index, $key, $this->config->field_type);
                }
                $toDate = function($val) use($field_type) {
                            $cb = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val));
                            return $field_type == 'datetime'
                                ? $cb->format("d.m.yy h:i:s")
                                : $cb->format("d.m.yy");
                     };

                $this->data[$this->config->field_code] = $this->b24CustomField->isMultiple()
                    ? array_map( $toDate, $value)
                    : $toDate($value) ;
                break;
            case 'enumeration' :
                if(!$validEnumValArr = $this->b24CustomField->getEnumIdsByValues($value) )  // //?? ["n0"];
                {
                    $this->throwCustomError($index,   sprintf('недопустимое значение поля "%s"' , $this->config->title) );
                }
                if($this->b24CustomField->isMultiple() && count($value)!=count($validEnumValArr))
                {
                    $this->throwCustomError($index,   sprintf('одно из значений поля "%s" является недопустимым' , $this->config->title) );
                }

                $this->data[$this->config->field_code] = $validEnumValArr;
                break;

            case 'crm_miltifield_child' :
                $newData = is_array($value) ? $value : explode(',', $value); // например два номера тел: 11111,22222
                $childFieldName = $this->config->field_code; // WORK
                $parentFieldName = $this->config->parent; // PHONE //$fields_config->where('parent', $config->parent);
                $current = collect(optional($this->b24Entity)[$parentFieldName]);
                // unset($this->data[$key]); // ["Телефон рабочий"] не нужен

                $_data = [];
                foreach ($current as $old_value) {
                    // тип совпадает
                    if ($old_value["VALUE_TYPE"] == $childFieldName) {
                        $old_value["VALUE"] = "";
                        $_data[] = $old_value;
                    }

                }
                foreach ($newData as $new_value) {
                    $_data[] = [
                        "VALUE_TYPE" => $childFieldName,
                        "VALUE" => $new_value,
                        "TYPE_ID" => $parentFieldName,
                    ];
                }

                $this->data[$parentFieldName] = $_data;

                break;


            default:
                //Кроме того, возможна ситуация, что значение ячейки содержится в списке запрещенных полей (отдельный признак в таблице полей). В этом случае данный столбец при импорте просто игнорируется без индикации ошибки.
                $this->data->forget($key);
                Log::channel('ext_debug')->debug("skip forbidden column:", [$value, $key]);

        }
    }

}
