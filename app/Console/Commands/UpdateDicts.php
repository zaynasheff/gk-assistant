<?php

namespace App\Console\Commands;

use App\Bitrix24\Bitrix24API;
use App\Bitrix24\Bitrix24APIException;
use App\Models\B24FieldsDictionary;
use App\Models\Entity;
use Exception;
use Illuminate\Console\Command;


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

            collect($this->b24->getDealFields())->each(function ($item, $key) {
                $this->updateDict($key, Entity::DEAL_ENTITY_ID, $item);
            });

            collect($this->b24->getLeadFields())->each(function ($item, $key) {

                $this->updateDict($key, Entity::LEAD_ENTITY_ID, $item);
            });

            collect($this->b24->getContactFields())->each(function ($item, $key) {
                $this->updateDict($key, Entity::CONTACT_ENTITY_ID, $item);
            });

            collect($this->b24->getCompanyFields())->each(function ($item, $key) {
                $this->updateDict($key, Entity::COMPANY_ENTITY_ID, $item);
            });



        } catch (Bitrix24APIException $e) {
            printf('Ошибка (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
        } catch (Exception $e) {
            printf('Ошибка (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
        }


        return 0;
    }

    private function updateDict($field_code, $entity_id, $dictItem)
    {
//UF_CRM_    listLabel
        B24FieldsDictionary::updateOrCreate(
            ['field_code' => $field_code, 'entity_id' => $entity_id],
            [
                'field_type' => $dictItem['type'],
                'required' => $dictItem['isRequired'],
                'title' => strpos( $field_code, "UF_CRM_") === 0
                    ? $dictItem["listLabel"]
                    : $dictItem['title'],
                'items' => json_encode($dictItem)
                //'forbidden_to_edit' => $dictItem['isReadOnly']
            ]);
    }
}
