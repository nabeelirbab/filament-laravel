<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasRoles, SoftDeletes, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'country',
        'surname',
        'affiliation',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relationships with papers, roles, etc.
    public function authoredPapers()
    {
        return $this->hasMany(Paper::class, 'author_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'referee_id');
    }
}
