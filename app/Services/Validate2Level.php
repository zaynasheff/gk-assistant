<?php


namespace App\Services;


use App\Exceptions\Validate2LevelException;
use App\Models\B24CustomFields;
use App\Models\B24FieldsDictionary;
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
            //пустое значение для поля, которое должно быть обязательным к заполнению;
            $value = trim($value);
            if (optional($this->config)->required && empty($value)
                && !$this->isNotAnException($this->config))
                throw new Validate2LevelException("Номер столбца:" . $index . "| ID сущности:" . $this->b24ID . "| Описание ошибки:" . $this->config->title . ' - обязательное поле');
            if (empty($value))    {
                $this->data->forget($key);
                Log::channel('ext_debug')->debug("skip empty column:", [$value, $key]);
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
    private function throw($col_ix, $field_name, $field_type)
    {
        throw new Validate2LevelException(
            sprintf('Номер столбца: %s | ID сущности: %s | Описание ошибки: Поле  %s не соответствует типу %s',
                $col_ix, $this->b24ID, $field_name, $field_type)
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


        $b24CustomField = new B24CustomFields(
            json_decode($this->config->items, true)
        );

        if ($b24CustomField->isMultiple()) {
            $value = array_map("trim", explode(",", $value));
        }

        unset($this->data[$key]);
        //несоответствие типов - содержимое ячейки не соответствует по типу полю сущности, с которым она ассоциирована;
        switch (optional($this->config)->field_type) {
            case 'integer' :
            case 'double' :
                if (!$this->validateNumeric($value)) {
                    $this->throw($index, $key, $this->config->field_type);
                }
                $this->data[$this->config->field_code] = $value;
                break;
            case 'string' :
                $this->data[$this->config->field_code] = $value;
                break;
            case 'boolean' :
                // if (!is_bool($value))
                if ($value != "Нет" && $value != "Да") {
                    $this->throw($index, $key, $this->config->field_type);
                }
                $this->data[$this->config->field_code] = $value;
                break;
            case 'datetime' :
                if (is_array($value) || !strtotime($value)) {
                    $this->throw($index, $key, $this->config->field_type);
                }
                $this->data[$this->config->field_code] = $value;
                break;
            case 'enumeration' :
                $this->data[$this->config->field_code] = $b24CustomField->getEnumIdsByValues($value); //?? ["n0"];
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
