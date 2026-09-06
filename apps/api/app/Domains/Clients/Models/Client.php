<?php

namespace App\Domains\Clients\Models;

use App\Domains\Clients\Enums\ClientStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'display_name',
    'company_or_institution',
    'email',
    'phone',
    'billing_name',
    'billing_email',
    'billing_phone',
    'billing_address',
    'status',
    'notes_internal',
])]
class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => ClientStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (empty($client->public_id)) {
                $client->public_id = (string) Str::ulid();
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_users')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function primaryUser(): ?User
    {
        return $this->users()->wherePivot('is_primary', true)->first()
            ?? $this->users()->first();
    }

    public function hasUser(User|int $user): bool
    {
        $userId = is_object($user) ? $user->id : $user;

        return $this->users()->where('users.id', $userId)->exists();
    }
}
