<?php


namespace App\Helpers;


use App\Bitrix24\Bitrix24APIException;
use App\Models\Entity;

class B24ExceptionHelper
{

    public function __construct(int $entity_id)
    {
        $this->entity_id = $entity_id;
    }

    public function interpeteErrorMsg(Bitrix24APIException $e)
    {

        return (strpos($e->getMessage(),
                '{"error":"","error_description":"Not found"}') !== false)
            ? sprintf('Несуществущий Б24 ID сущности %s', Entity::getName($this->entity_id))
            : $e->getMessage() ;



    }
}
