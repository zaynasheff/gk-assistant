<?php


namespace App\Services;


use App\Exceptions\Validate2LevelException;
use App\Models\B24CustomFields;
use App\Models\B24FieldsDictionary;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class Validate2Level
 * @package App\Services
 */
class Validate2Level
{
    /**
     * @var int
     */
    private $entity_id;
    /**
     * @var Collection
     */
    private $data;
    /**
     * @var int
     */
    private $row_n;

    public function __construct(array $data, int $entity_id, int $row_n)
        {
            $this->data = collect($data);
            $this->entity_id = $entity_id;
            $this->row_n = $row_n;
        }

    /**
     * @param array $b24Entity
     * @return  Collection
     * @throws Validate2LevelException
     */
    public function validateData(array $b24Entity, Collection $fields_config): Collection
    {
        Log::channel('ext_debug')->debug("start validating. ");

        $this->data->each(function ($value, $key) use ($b24Entity, $fields_config) {
            $index = $this->data->keys()->search($key) + 1;
            $config = $fields_config->where('title', $key)->first();
            //пустое значение для поля, которое должно быть обязательным к заполнению;
            if (optional($config)->required && empty(trim($value))
            && !$this->isNotAnException($config) )
                throw new Validate2LevelException("Номер столбца:" . $index . "| ID сущности:" . $this->data["ID"] . "| Описание ошибки:" . $config->title . ' - обязательное поле');
            if(!empty(trim($value))) {
                //несоответствие типов - содержимое ячейки не соответствует по типу полю сущности, с которым она ассоциирована;
                switch (optional($config)->field_type) {
                    case 'integer' :
                        if (!is_numeric($value)) throw new Validate2LevelException("Номер столбца:" . $index . "| ID сущности:" . $this->data["ID"] . "| Описание ошибки:" . 'Поле ' . $key . ' не соответствует типу integer');

                        unset($this->data[$key]);
                        $this->data[$config->field_code] = $value;
                        break;
                    case 'string' :
                        unset($this->data[$key]);
                        $this->data[$config->field_code] = $value;
                        break;
                    case 'boolean' :

                        if (!is_bool($value)) throw new Validate2LevelException("Номер столбца:" . $index . "| ID сущности:" . $this->data["ID"]  . "| Описание ошибки:" . 'Поле ' . $key . ' не соответствует типу boolean');
                        unset($this->data[$key]);
                        $this->data[$config->field_code] = $value;
                        break;
                    case 'double' :
                        if (!is_numeric($value)) throw new Validate2LevelException("Номер столбца:" . $index . "| ID сущности:" . $this->data["ID"]  . "| Описание ошибки:" . 'Поле ' . $key . ' не соответствует типу double');
                        unset($this->data[$key]);
                        $this->data[$config->field_code] = $value;
                        break;
                    case 'datetime' :
                        if (!strtotime($value)) throw new Validate2LevelException("Номер столбца:" . $index . "| ID сущности:" . $this->data["ID"]  . "| Описание ошибки:" . 'Поле ' . $key . ' не соответствует типу datetime');
                        unset($this->data[$key]);
                        $this->data[$config->field_code] = $value;
                        break;
                    case 'enumeration' :
                        $b24CustomFields = new B24CustomFields(
                            json_decode($config->items, true)
                        );
                        unset($this->data[$key]);
                        $this->data[$config->field_code] = $b24CustomFields->getEnumIdByValue($this->data[$key]);
                        break;
                    case 'crm_miltifield_child' :
                        $newData = explode(',', $value); // например два номера тел: 11111,22222
                        $childFieldName = $config->field_code; // WORK
                        $parentFieldName = $config->parent; // PHONE //$fields_config->where('parent', $config->parent);
                        $current = collect(optional($b24Entity)[$parentFieldName]);
                        unset($this->data[$key]); // ["Телефон рабочий"] не нужен

                        $_data = [];
                        foreach($current as $old_value) {
                            // тип совпадает
                            if($old_value["VALUE_TYPE"] == $childFieldName) {
                                $old_value["VALUE"] = "";
                                $_data[] = $old_value;
                            }

                        }
                        foreach($newData as $new_value) {
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
            }  else {
                $this->data->forget($key);
                Log::channel('ext_debug')->debug("skip empty column:", [$value, $key]);

            }

        });

        Log::channel('ext_debug')->debug("validated data:", $this->data->toArray());

        return $this->data;


    }

    private function isNotAnException(B24FieldsDictionary $dict) : bool
    {
        if($dict->entity_id == 3 && ( $dict->title=='Фамилия' || $dict->title=='Отчество' ))
            return true; // Фамилия , Отчество для Контакта

        return false;
    }

    /**
     * @throws Validate2LevelException
     */
    public function validateID($b24ID)
    {
        if(!is_numeric($b24ID)) {
            $index = $this->data->keys()->search("ID") + 1;
            throw new Validate2LevelException("Номер столбца:" . (string)$index . "| ID сущности:" . $this->data["ID"]  . "| Описание ошибки:" . 'Поле ID не соответствует типу integer');
        }
    }

}
