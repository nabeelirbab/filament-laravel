<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'paper_id',
        'referee_id',
        'comments',
        'decision',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function referee()
    {
        return $this->belongsTo(User::class, 'referee_id');
    }
}
