<?php

namespace App\Models;

use App\Mail\Visualbuilder\EmailTemplates\UserRequestReset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Notifications\UserResetPasswordRequestNotification;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

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

    public function sendPasswordResetNotification($token)
    {
        \Log::info('Custom Password Reset Called for: ' . $this->email);

        $tokenHelper = app(TokenHelperInterface::class);


        $url = \Illuminate\Support\Facades\URL::secure(route('password.reset', ['token' => $token, 'email' => $this->email]));
        $user = (object) [
            'url' => $url,
            'email' => $this->email,
        ];
        Mail::to($this->email)->send(new UserRequestReset($user, $tokenHelper));
        // $this->notify(new UserResetPasswordRequestNotification($url));
    }

    public function papersAsEditor()
    {
        return $this->hasMany(Paper::class, 'associate_editor_id');
    }
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
