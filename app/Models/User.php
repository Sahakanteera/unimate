<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'student_id',
        'email',
        'password',
        'role',
        'status',
        'bio',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function initials(): string
    {
        $words = explode(' ', trim($this->name));
        $first = mb_substr($words[0] ?? '', 0, 1, 'UTF-8');
        $last = isset($words[1]) ? mb_substr($words[1], 0, 1, 'UTF-8') : '';
        return strtoupper($first . $last);
    }
}
