<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonExperience extends Model
{
    protected $guarded = ['id'];

    protected $fillable = [
        'job_title',
        'job_start_year',
        'job_end_date',
        'center_id',
        'person_id',
    ];

  

    public function center(){
        return $this->belongsTo(Center::class);
    }
}
