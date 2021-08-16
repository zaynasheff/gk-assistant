<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/*
 * @method fieldsUpdatedBefore($query, $updateTime)
 */
class B24FieldsDictionary extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function scopeEntityFieldsUpdatedBefore($query, $updateTime, $enity_id)
    {
        return $query ->where('entity_id', $enity_id)
                       ->where('field_type', '!=' , 'crm_miltifield_child')
                      ->where('updated_at', '<', $updateTime  )
                      ->where('created_at', '<', $updateTime );
    }
}
