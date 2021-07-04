<?php


namespace App\Services;


use App\Models\B24CustomFields;
use App\Models\B24FieldsDictionary;
use Exception;
use Illuminate\Support\Collection;

class Validate2Level
{


    /**
     * @param array $data
     * @return  Collection
     * @throws Exception
     */
    public static function validateData(array $data, int $entity_id, array $b24Entity): Collection
    {

        $fields_config = B24FieldsDictionary::where('entity_id', $entity_id)->get();

        $data = collect($data);

        $data->each(function ($value, $key) use ($b24Entity, &$data, $fields_config, $entity_id) {
            $config = $fields_config->where('title', $key)->first();
            //пустое значение для поля, которое должно быть обязательным к заполнению;
            if (optional($config)->required && empty(trim($value)))
                throw new Exception("Наименование поля:" . $config->title . "| ID сущности:" . $entity_id. "| Описание ошибки:" . $config->title . ' - обязательное поле');
            if(!empty(trim($value))) {
                //несоответствие типов - содержимое ячейки не соответствует по типу полю сущности, с которым она ассоциирована;
                switch (optional($config)->field_type) {
                    case 'integer' :
                        if (!is_numeric($value)) throw new Exception("Наименование поля:" . $config->title . "| ID сущности:" . $entity_id . "| Описание ошибки:" . $this->entity->id . "|" . 'Поле ' . $key . ' не соотвестввует типу integer');

                        unset($data[$key]);
                        $data[$config->field_code] = $value;
                        break;
                    case 'string' :
                        unset($data[$key]);
                        $data[$config->field_code] = $value;
                        break;
                    case 'boolean' :

                        if (!is_bool($value)) throw new Exception("Наименование поля:" . $config->title . "| ID сущности:" . $entity_id . "| Описание ошибки:" . 'Поле ' . $key . ' не соотвестввует типу boolean');
                        unset($data[$key]);
                        $data[$config->field_code] = $value;
                        break;
                    case 'double' :
                        if (!is_numeric($value)) throw new Exception("Наименование поля:" . $config->title . "| ID сущности:" . $entity_id . "| Описание ошибки:" . 'Поле ' . $key . ' не соотвестввует типу double');
                        unset($data[$key]);
                        $data[$config->field_code] = $value;
                        break;
                    case 'datetime' :
                        if (!strtotime($value)) throw new Exception("Наименование поля:" . $config->title . "| ID сущности:" . $entity_id . "| Описание ошибки:" . 'Поле ' . $key . ' не соотвестввует типу datetime');
                        unset($data[$key]);
                        $data[$config->field_code] = $value;
                        break;
                    case 'enumeration' :
                        $b24CustomFields = new B24CustomFields(
                            json_decode($config->items, true)
                        );
                        unset($data[$key]);
                        $data[$config->field_code] = $b24CustomFields->getEnumIdByValue($data[$key]);
                        break;
                    case 'crm_miltifield_child' :
                        $newData = explode(',', $value); // например два номера тел: 11111,22222
                        $childFieldName = $config->field_code; // WORK
                        $parentFieldName = $config->parent; // PHONE //$fields_config->where('parent', $config->parent);
                        $current = collect(optional($b24Entity)[$parentFieldName]);
                        unset($data[$key]); // ["Телефон рабочий"] не нужен

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

                        $data[$parentFieldName] = $_data;

                        break;


                    default:
                        //Кроме того, возможна ситуация, что значение ячейки содержится в списке запрещенных полей (отдельный признак в таблице полей). В этом случае данный столбец при импорте просто игнорируется без индикации ошибки.
                        $data->forget($key);
                }
            }  else {
                $data->forget($key);

            }

        });


        return $data;


    }
}
