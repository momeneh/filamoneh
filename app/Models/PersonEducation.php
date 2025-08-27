<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonEducation extends Model
{
    protected $guarded = ['id'];

    protected $fillable = [
        'grade_id',
        'field_id',
        'start_year',
        'end_year',
        'center_id',
        'person_id',
        'not_in_list',
        'other_center'
    ];

    public function grade(){
        return $this->belongsTo(Grade::class);
    }

    public function field(){
        return $this->belongsTo(Field::class);
    }

    public function center(){
        return $this->belongsTo(Center::class);
    }
}
