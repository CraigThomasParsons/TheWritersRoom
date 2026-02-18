<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LlmModel extends Model
{
    protected $fillable = [
        'provider',
        'model_key',
        'display_name',
        'priority',
        'is_enabled',
        'supports_code',
        'health_status',
        'last_success_at',
        'last_error_at',
        'last_error_message',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'supports_code' => 'boolean',
        'last_success_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
