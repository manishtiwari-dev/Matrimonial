<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CareerInfo extends Model
{

   
    public function positionHeld()
    {
        return $this->belongsTo(PositionHeld::class,'position');
    }

}
