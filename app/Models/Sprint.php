<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'goal',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    public static function boot()
    {
        parent::boot();

        static::updating(function ($sprint) {
            if ($sprint->getOriginal('status') === 'ready') {
                throw new \Exception('Cannot modify a sprint that is marked as ready. Sprint is immutable.');
            }
        });
    }
}
