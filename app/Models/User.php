<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable implements MustVerifyEmail
{


    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile',
        'profile_pic',
        'date_of_birth',
        'provider',
        'google_id',
        'password',
        'created_by',
        'deleted_by'
    ];




    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'archive' => 'array',
        ];
    }


    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function employer()
    {
        return $this->hasOne(Employer::class);
    }


    public function logs()
    {
        return $this->hasMany(AdminLog::class);
    }

    public function employerTeamMembers()
    {
        return $this->hasMany(EmployerTeamMember::class);
    }


    public function userBadges()
    {
        return $this->hasMany(UserBadge::class);
    }



    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }


    public function courses()
    {
        return $this->hasMany(Course::class, 'instructor');
    }

    public function projectProposals()
    {
        return $this->hasMany(ProjectProposal::class, 'freelancer_id');
    }
}
