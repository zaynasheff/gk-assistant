<?php

namespace App\Services;

use App\Bitrix24\Bitrix24API;
use App\Models\Entity;
use Exception;

class Bitrix24ConcreteMethodFactory {

    /**
     * @var int
     */
    private $entity_id;
    /**
     * @var Bitrix24API
     */
    private $bitrix24API;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(int $entity_id)
    {
        $this->entity_id = $entity_id;
        $this->bitrix24API = app()->make(Bitrix24API::class); //Bitrix24API $bitrix24API
    }

    /**
     * @param ...$data
     * @return mixed
     * @throws Exception
     */
    function GetOne(...$data)  {
        switch($this->entity_id) {
            case Entity::DEAL_ENTITY_ID:
                return  $this->bitrix24API->getDeal(...$data);
            case Entity::LEAD_ENTITY_ID:
                return  $this->bitrix24API->getLead(...$data);
            case Entity::COMPANY_ENTITY_ID:
                return  $this->bitrix24API->getCompany(...$data);
            case Entity::CONTACT_ENTITY_ID:
                return  $this->bitrix24API->getContact(...$data);

            default: throw new \LogicException($this->entity_id . ' - некорректный тип сущности');
        }
    }

    /**
     * @param ...$data
     * @return mixed
     * @throws Exception
     */
    function UpdateOne(...$data) {
        switch($this->entity_id) {
            case Entity::DEAL_ENTITY_ID:
                return  $this->bitrix24API->updateDeal(...$data);
            case Entity::LEAD_ENTITY_ID:
                return  $this->bitrix24API->updateLead(...$data);
            case Entity::COMPANY_ENTITY_ID:
                return  $this->bitrix24API->updateCompany(...$data);
            case Entity::CONTACT_ENTITY_ID:
                return  $this->bitrix24API->updateContact(...$data);

            default: throw new \LogicException($this->entity_id . ' - некорректный тип сущности');
        }
    }

}
