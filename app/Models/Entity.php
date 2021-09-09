<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use HasFactory;

    const DEAL_ENTITY_ID = 1; // id  в entities
    const LEAD_ENTITY_ID = 2;
    const CONTACT_ENTITY_ID = 3;
    const COMPANY_ENTITY_ID = 4;


    public static function getName($entity_id)
    {
       switch($entity_id)
       {
           case self::DEAL_ENTITY_ID: return 'сделка';
           case self::LEAD_ENTITY_ID: return 'заявка';
           case self::CONTACT_ENTITY_ID: return 'контакт';
           case self::COMPANY_ENTITY_ID: return 'компания';

           default: return 'n/a'; //TODO EXCEPT ?
       }
    }

}
