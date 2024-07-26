<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerExpectation extends Model
{
    protected $casts = [
        'language' => 'array'
    ];



    public function religion()

    {
        return $this->belongsTo(ReligionInfo::class,'religion');
    }


    public function mother_tongue()
    {
        return $this->belongsTo(MotherTongue::class,'mother_tongue');
    }


    public function community()
    {
        return $this->belongsTo(Community::class,'community');
    }



    public function profession()
    {
        return $this->belongsTo(Profession::class,'profession');
    }

    
  

    public function maritialStatus()
    {
        return $this->belongsTo(MaritalStatus::class,'marital_status');
    }




    public function positionHeld()
    {
        return $this->belongsTo(PositionHeld::class,'position');
    }


    
    
    public function smoking()
    {
        return $this->belongsTo(Smoking::class,'smoking_status');
    }


    public function drinking()
    {
        return $this->belongsTo(Drinking::class,'drinking_status');
    }






}
