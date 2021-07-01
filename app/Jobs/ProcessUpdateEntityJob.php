<?php

namespace App\Jobs;

use App\Bitrix24\Bitrix24API;
use App\Models\B24FieldsDictionary;
use App\Models\Entity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class ProcessUpdateEntityJob
 * @package App\Jobs
 *
 *
 */
class ProcessUpdateEntityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    const FORBIDDEN_TYPES = [
        'iblock_section',
        'iblock_element',
        'employee',
        'crm_status',
        'crm',
    ];

    /**
     * @var Collection
     */
    private $data;
    /**
     * @var Entity
     */
    private $entity;


    public function __construct(int $current_row_n, int $entity_id, array $data)
    {

        $this->entity = Entity::find($entity_id);

        try {
            $this->data = $this->validate($data);

        } catch (\Exception $e) {
           // Если одна из проверок возвратила ошибку, то пишем ее в файл лога в одну строку формате:
           // Номер_строки | Номер_столбца | IDсущности | Описание_ошибки
           //  Пример: 12312 ABX ID321321 поле “Сумма сделки” пусто

            Log::info( 'Номер_строки:' . $current_row_n  . "|" .  $e->getMessage());

        }

    }

    /**
     * @param array $data
     * @return  Collection
     * @throws Exception
     */
    private function validate(array $data) : Collection
    {

        $fields_config = B24FieldsDictionary::where('entity_id', $this->entity->id)->get();

        $data = collect($data);
        $data->each(function ($value, $key) use (&$data, $fields_config) {
                $config = $fields_config->where('title', $key)->first();
                //пустое значение для поля, которое должно быть обязательным к заполнению;
                if ($config->required && empty(trim($value)))
                    throw new Exception( "Наименование поля:" . $config->title . "| ID сущности:" . $this->entity->id . "| Описание ошибки:" . $config->title . ' - обязательное поле');
                //несоответствие типов - содержимое ячейки не соответствует по типу полю сущности, с которым она ассоциирована;
                switch ($config->field_type) {
                    case 'integer' :
                        if (!is_numeric($value)) throw new Exception("Наименование поля:" . $config->title . "| ID сущности:" . $this->entity->id . "| Описание ошибки:" . $this->entity->id . "|" . 'Поле ' . $key . ' не соотвестввует типу integer');
                        break;
                    case 'string' :
                    case 'boolean' :
                        if (!is_bool($value)) throw new Exception("Наименование поля:" . $config->title . "| ID сущности:" . $this->entity->id . "| Описание ошибки:" . 'Поле ' . $key . ' не соотвестввует типу boolean');
                        break;
                    case 'double' :
                        if (!is_float($value)) throw new Exception("Наименование поля:" . $config->title . "| ID сущности:" . $this->entity->id . "| Описание ошибки:" . 'Поле ' . $key . ' не соотвестввует типу boolean');
                        break;
                    case 'datetime' :
                        if (!strtotime($value)) throw new Exception("Наименование поля:" . $config->title . "| ID сущности:" . $this->entity->id . "| Описание ошибки:" . 'Поле ' . $key . ' не соотвестввует типу datetime');
                        break;
                    default:
                        //Кроме того, возможна ситуация, что значение ячейки содержится в списке запрещенных полей (отдельный признак в таблице полей). В этом случае данный столбец при импорте просто игнорируется без индикации ошибки.
                    $data->forget($key);
                }
            });


            return $data;




    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Bitrix24API $bitrix24API)
    {
        //
    }
}
