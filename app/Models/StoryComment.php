<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores human or Piper comments attached to a story.
 */
class StoryComment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'story_id',
        'author_name',
        'author_type',
        'message',
        'metadata',
    ];

    /**
     * Ensure metadata is treated as an array when accessed.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Fetch the story this comment belongs to.
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }
}
