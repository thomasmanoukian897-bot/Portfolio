<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordChangeVerification extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'code',
        'new_password',
        'expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function matchesCode(string $code): bool
    {
        return Hash::check($code, $this->code);
    }

    public function pendingPassword(): string
    {
        return Crypt::decryptString($this->new_password);
    }

    public static function createForUser(User $user, string $newPassword): array
    {
        static::query()->where('user_id', $user->id)->delete();

        $plainCode = Str::padLeft((string) random_int(0, 999999), 6, '0');

        $verification = static::query()->create([
            'user_id' => $user->id,
            'code' => Hash::make($plainCode),
            'new_password' => Crypt::encryptString($newPassword),
            'expires_at' => now()->addMinutes(15),
        ]);

        return [
            'verification' => $verification,
            'plainCode' => $plainCode,
        ];
    }

    public static function findActiveForUser(User $user): ?self
    {
        return static::query()
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();
    }
}
