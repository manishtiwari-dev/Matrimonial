<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BasicInfo extends Model
{
    protected $casts = [
        'present_address' => 'object',
        'permanent_address' => 'object',
        'language' => 'array',
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

    
    public function bloodGroups()
    {
        return $this->belongsTo(BloodGroup::class,'blood_group');
    }


    public function maritialStatus()
    {
        return $this->belongsTo(MaritalStatus::class,'marital_status');
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
