<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Epic extends Model
{
    use HasFactory;

    protected $appends = ['description'];

    protected $fillable = [
        'title',
        'description',
        'summary',
        'epic_status_id',
        'chat_project_id',
    ];

    /**
     * Get the project this epic belongs to.
     * 
     * This is the local projects table (synced from ChatProjects).
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'chat_project_id');
    }

    /**
     * Get the ChatProject this epic belongs to (cross-database).
     *
     * Note: This is a cross-database relationship to ChatProjects.
     * Prefer using project() for local queries.
     */
    public function chatProject(): BelongsTo
    {
        return $this->belongsTo(ChatProject::class, 'chat_project_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(EpicStatus::class, 'epic_status_id');
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    public function getStoryCountAttribute(): int
    {
        return $this->stories()->count();
    }

    public function getDoneStoryCountAttribute(): int
    {
        return $this->stories()
            ->whereHas('status', fn ($q) => $q->where('key', 'done'))
            ->count();
    }

    public function getProgressPercentAttribute(): int
    {
        $total = $this->story_count;
        if ($total === 0) return 0;
        return (int) round(($this->done_story_count / $total) * 100);
    }

    protected static function booted(): void
    {
        static::creating(function (Epic $epic): void {
            if (! $epic->epic_status_id) {
                $epic->epic_status_id = EpicStatus::query()->firstOrCreate(
                    ['key' => 'backlog'],
                    ['name' => 'Backlog']
                )->id;
            }
        });
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->summary,
            set: fn (?string $value): array => ['summary' => $value]
        );
    }
}
