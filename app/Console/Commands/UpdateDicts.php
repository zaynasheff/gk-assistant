<?php

namespace App\Console\Commands;

use App\Bitrix24\Bitrix24API;
use App\Bitrix24\Bitrix24APIException;
use App\Models\B24FieldsDictionary;
use App\Models\Entity;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;


class UpdateDicts extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dicts:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * @var Bitrix24API
     */
    private $b24;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Bitrix24API $bitrix24API)
    {
        $this->b24 = $bitrix24API;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {


        try {

           // dd($this->b24->getCompany(1015));
           // dd($this->b24->getDealFields());

            $this->updateDict(
                collect($this->b24->getDealFields())->transformEntityFields(
                    Entity::DEAL_ENTITY_ID
                ), Entity::DEAL_ENTITY_ID
            );
            $this->updateDict(
                collect($this->b24->getLeadFields())->transformEntityFields(
                    Entity::LEAD_ENTITY_ID
                ), Entity::LEAD_ENTITY_ID
            );
            $this->updateDict(
                collect($this->b24->getContactFields())->transformEntityFields(
                    Entity::CONTACT_ENTITY_ID
                ), Entity::CONTACT_ENTITY_ID
            );
            $this->updateDict(
                collect($this->b24->getCompanyFields())->transformEntityFields(
                    Entity::COMPANY_ENTITY_ID
                ), Entity::COMPANY_ENTITY_ID
            );



        } catch (Bitrix24APIException $e) {
            printf('Ошибка (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
        } catch (Exception $e) {
            printf('Ошибка (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
        }


        return 0;
    }

    private function updateDict(Collection $data, int $entity_id)
    {
//UF_CRM_    listLabel
        $updateTime = now()->toDateTimeString();

        B24FieldsDictionary::upsert( $data->toArray(),
            ['field_code', 'entity_id'],
            ['field_type', 'required', 'title', 'items', 'field_type']);

        B24FieldsDictionary::entityFieldsUpdatedBefore($updateTime, $entity_id)
            ->delete();
    }
}
