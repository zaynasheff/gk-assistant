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
}
