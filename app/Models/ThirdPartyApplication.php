<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ThirdPartyApplication extends Model
{
    protected $fillable = [
        'app_name',
        'company_name',
        'contact_email',
        'contact_phone',
        'description',
        'api_key',
        'api_secret',
        'is_active',
        'discount',
        'last_used_at',
        'request_count',
        'webhook_url',
        'allowed_ips',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'discount' => 'decimal:2',
        'last_used_at' => 'datetime',
        'allowed_ips' => 'array',
    ];

    /**
     * Generate a new API key
     */
    public static function generateApiKey(): string
    {
        return Str::random(64);
    }

    /**
     * Generate a new API secret
     */
    public static function generateApiSecret(): string
    {
        return Str::random(64);
    }

    /**
     * Find application by API key
     */
    public static function findByApiKey(string $apiKey): ?self
    {
        return static::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Increment request count and update last used timestamp
     */
    public function recordApiRequest(): void
    {
        $this->increment('request_count');
        $this->update(['last_used_at' => now()]);
    }
}

