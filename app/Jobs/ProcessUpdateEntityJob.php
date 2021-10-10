<?php

namespace App\Jobs;

use App\Bitrix24\Bitrix24API;
use App\Bitrix24\Bitrix24APIException;
use App\Exceptions\Validate2LevelException;
use App\Helpers\B24ExceptionHelper;
use App\Models\B24FieldsDictionary;
use App\Models\ProcessHistory;
use App\Services\Bitrix24ConcreteMethodFactory;
use App\Services\Validate2Level;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

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
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    private $data;
    private $entity_id;

    /**
     * @var int
     */
    private $current_row_n;
    /**
     * @var mixed
     */
    private $b24ID;
    /**
     * @var ProcessHistory
     */
    private $processHistory;


    public function __construct(int $current_row_n, int $entity_id, array $data, ProcessHistory $processHistory)
    {

        $this->entity_id = $entity_id;
        $this->data = $data;
        $this->current_row_n = $current_row_n + 1; // с учетом заголовка
        $this->b24ID = $data["ID"];
        $this->processHistory = $processHistory;

    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(Bitrix24API $bitrix24API) // ProcessingImportIF $process
    {

        //$this->process = $process;
        //$this->b24 = $bitrix24API;
         if(app()->runningUnitTests()) {
            $this->doTheJob();
            return;
        }

        Redis::throttle('key')->block(0)->allow(60)->every(60)->then(function () {
            // info('Lock obtained...');
            $this->doTheJob();

        }, function () {
            // Could not obtain lock...

            return $this->release(10);
        });


    }

    private function doTheJob()
    {
        $error = null;
        $process = $this->processHistory;

        Log::channel('ext_debug')->debug("start new ProcessUpdateEntityJob: ",
            [
                'current_row_n' => $this->current_row_n,
                'entity_id' => $this->entity_id,
                'b24ID' => $this->b24ID,
                'data' => $this->data,
            ]
        );


        try {
            //$entityName = $this->validateEntity();
            $validator = new Validate2Level($this->data);
            $validator->validateID();

            $b24MethodFactory = new Bitrix24ConcreteMethodFactory($this->entity_id); //$bitrix24API);

            $fields_config = B24FieldsDictionary::where('entity_id', $this->entity_id)->get();

            // нужно запросить сущность из б24, в случае если мы будем работать с miltifield
            $b24Entity =  $fields_config->whereIn('title', array_keys($this->data) )
                ->whereIn('field_type', ['crm_miltifield_child', 'crm_multifield'])
                ->IsnotEmpty()
                ? $b24MethodFactory->GetOne($this->b24ID)
                : [];

            $b24MethodFactory->UpdateOne($this->b24ID,
                $validator->validateData($b24Entity, $fields_config)->toArray()
            );

            $process->increment('lines_success');
            Log::channel('ext_debug')->debug("increment lines_success:" . $process->lines_success);


        } catch (Validate2LevelException $e) {
            // Если одна из проверок возвратила ошибку, то пишем ее в файл лога в одну строку формате:
            // Номер_строки | Номер_столбца | IDсущности | Описание_ошибки
            //  Пример: 12312 ABX ID321321 поле “Сумма сделки” пусто

            $error = 'Номер строки:' . $this->current_row_n . "|" . $e->getMessage(); //. ";файл:" . $e->getFile() . ";строка:" . $e->getLine();
            Storage::disk('log')->append('update.log', $error);
            $process->increment('lines_error');
            Log::channel('ext_debug')->debug("increment lines_error:" . $process->lines_error);

        } catch (Bitrix24APIException $e) {
            $error = 'Номер строки:' . $this->current_row_n . "| Номер столбца: -| ID сущности: " . $this->b24ID . "| Описание ошибки при обращении в Б24:"
                . (new B24ExceptionHelper($this->entity_id))->interpeteErrorMsg($e);
            Storage::disk('log')->append('update.log', $error);
            $process->increment('lines_error');
            Log::channel('ext_debug')->debug("b24 exception:" . $error . ";файл:" . $e->getFile() . ";строка:" . $e->getLine() );
            Log::channel('ext_debug')->debug("increment lines_error:" . $process->lines_error);

        } catch (\Exception $e) {

            $error = 'Номер строки:' . $this->current_row_n . "| Номер столбца: -| ID сущности: " . $this->b24ID . "| Описание необрабатываемой ошибки:" . $e->getMessage();
            Storage::disk('log')->append('update.log', $error);
            $process->increment('lines_error');
            Log::channel('ext_debug')->debug("unprocessed exception:" . $error . ";файл:" . $e->getFile() . ";строка:" . $e->getLine() . ";трейс:" . $e->getTraceAsString() );
            Log::channel('ext_debug')->debug("increment lines_error:" . $process->lines_error);

        }


        if ($this->current_row_n >= $process->lines_count) {
            $process->processing = 3;
            $process->process_end = now()->toDateTimeString();
            $process->save();
            Log::channel('ext_debug')->debug("finish ProcessUpdateEntityJob:",
                [
                    'current_row_n' => $this->current_row_n,
                ]
            );

        }

/*        if ($error) {
            // чтобы повторить попытку из очереди нужно выкинуть экс так или иначе
            // попыток у нас нет, но по крайней мере увидим что-то в failed_jobs
            throw new Exception($error);
        }*/


    }


}
