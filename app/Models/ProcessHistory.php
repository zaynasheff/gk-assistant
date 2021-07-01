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
        if ($value === null){
            return 'расчет времени завершения...';
        }
        return Carbon::parse($value)->format('d.m.Y H:i:s');
    }

    public function entity(){
        return $this->hasOne(Entity::class,'id','entity_id');
    }

    public static function isRunning(){
        if(self::where('processing',1)->first()){
            return true;
        }
        return false;
    }
}
