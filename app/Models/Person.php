<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Person extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    protected $guarded = ['id'];

    protected $fillable = [ 
        'name',
        'family',
        'email',
        'national_code',
        'shenasname',
        'passport_number',
        'father_name',
        'birth_year',
        'photo',
        'website',
        'mobile',
        'tel',
        'fax',
        'postalcode',
        'addr',
        'country_id',
        'province_id',
        'city_id',
        'gender',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    
    public function PersonEducation()
    {
        return $this->hasMany(PersonEducation::class);
    }

    public function PersonExperience(){
        return $this->hasMany(PersonExperience::class);
    }


}
