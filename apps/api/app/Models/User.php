<?php

namespace App\Models;

use App\Domains\Clients\Models\Client;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Workers\Models\WorkerProfile;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => UserStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->public_id)) {
                $user->public_id = (string) Str::ulid();
            }
        });
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_users')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function primaryClient(): ?Client
    {
        return $this->clients()->wherePivot('is_primary', true)->first()
            ?? $this->clients()->first();
    }

    public function workerProfile(): HasOne
    {
        return $this->hasOne(WorkerProfile::class);
    }
}
