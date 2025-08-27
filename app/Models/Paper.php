<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\PaperObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
 
#[ObservedBy([PaperObserver::class])]
class Paper extends Model
{
    protected $guarded = ['id'];

    protected $fillable = [
        'title',
        'paper_type_id',
        'country_id',
        'title_url',
        'priority',
        'image_path1',
        'image_path2',
        'paper_file',
        'paper_word_file',
        'paper_date',
        'doi',
        'count_page',
        'refrence_link',
        'is_accepted',
        'is_visible',
        'is_archived',
        'abstract',
        'description',
        'insert_user_id',
        'edit_user_id'
    ];

    protected $casts = [
        'is_accepted' => 'boolean',
        'is_visible' => 'boolean',
        'is_archived' => 'boolean',
    ];

    public function inserter()
    {
        return $this->belongsTo(User::class, 'insert_user_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'edit_user_id');
    }

    public function paperType()
    {
        return $this->belongsTo(PaperType::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function paperSubject(){
        return $this->belongsToMany(Subject::class,'paper_subjects');
    }

    /**
     * Get the full URL for the paper file
     */
    public function getPaperFileUrlAttribute()
    {
        return $this->paper_file ? asset('storage/' . $this->paper_file) : null;
    }

    /**
     * Get the full URL for the Word file
     */
    public function getPaperWordFileUrlAttribute()
    {
        return $this->paper_word_file ? asset('storage/' . $this->paper_word_file) : null;
    }

    /**
     * Get the full URL for image 1
     */
    public function getImagePath1UrlAttribute()
    {
        return $this->image_path1 ? asset('storage/' . $this->image_path1) : null;
    }

    /**
     * Get the full URL for image 2
     */
    public function getImagePath2UrlAttribute()
    {
        return $this->image_path2 ? asset('storage/' . $this->image_path2) : null;
    }

    public function tags(){
        return $this->belongsToMany(Tag::class,'paper_tags');
    }

    public function paperResource(){
        return $this->hasMany(PaperResource::class);
    }
}
