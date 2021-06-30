<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessHistory extends Model
{
    use HasFactory;

    protected $guarded=[];

    public function getProcessStartAttribute($value){
        return Carbon::parse($value)->format('d.m.Y H:i:s');
    }

    public function getProcessEndAttribute($value){
        return Carbon::parse($value)->format('d.m.Y H:i:s');
    }

    public function entity(){
        return $this->hasOne(Entity::class,'id','entity_id');
    }
}
