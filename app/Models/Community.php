<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Community extends Model
{

    protected $table = "communities";


    public function religion()

    {
        return $this->belongsTo(ReligionInfo::class,'religion_id');
    }


}




