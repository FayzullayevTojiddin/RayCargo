<?php
namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable, HasApiTokens;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function getFilamentName(): string
    {
        return $this->email;
    }
    protected $fillable = [
        'email',
        'phone_number',
        'image',
        'password',
        'login_type',
        'last_login_at',
        'role',
        'status',
        'lang',
        'email_verified_at',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'role' => UserRole::class,
        'status' => UserStatus::class,
        'password' => 'hashed',
    ];

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->wallet()->create([
                'balance' => 0,
            ]);
        });
    }

    public function client(): HasOne
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function walletTransactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            WalletTransaction::class,
            Wallet::class,
            'user_id',
            'wallet_id',
            'id',
            'id'
        );
    }
}
