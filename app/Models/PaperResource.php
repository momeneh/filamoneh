<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaperResource extends Model
{
    protected $fillable = [
        'title',
        'link',
        'paper_id'
    ];
}
