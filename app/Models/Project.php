<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /**
     * The primary key is not auto-incrementing (synced from ChatProjects).
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'name',
        'code_folder',  // Kept for backwards compat, prefer repositories()
        'description',
        'synced_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'synced_at' => 'datetime',
    ];

    /**
     * Get the repositories belonging to this project.
     */
    public function repositories(): HasMany
    {
        return $this->hasMany(ProjectRepository::class)->orderBy('display_order');
    }

    /**
     * Get the primary repository for this project.
     */
    public function primaryRepository()
    {
        return $this->repositories()->where('is_primary', true)->first();
    }

    /**
     * Get repositories by role.
     */
    public function repositoriesByRole(string $role)
    {
        return $this->repositories()->where('role', $role);
    }

    /**
     * Get the epics belonging to this project.
     */
    public function epics(): HasMany
    {
        return $this->hasMany(Epic::class, 'chat_project_id');
    }

    /**
     * Get all stories for this project (through epics).
     */
    public function stories()
    {
        return Story::whereIn('epic_id', $this->epics()->pluck('id'));
    }

    /**
     * Get stories ready for development.
     */
    public function readyStories()
    {
        return $this->stories()
            ->whereHas('status', fn ($q) => $q->where('key', 'ready_for_dev'));
    }

    /**
     * Sync from ChatProjects data.
     */
    public static function syncFromChatProjects(array $data): self
    {
        return static::updateOrCreate(
            ['id' => $data['id']],
            [
                'name' => $data['name'],
                'code_folder' => $data['code_folder'] ?? null,
                'description' => $data['description'] ?? null,
                'synced_at' => now(),
            ]
        );
    }
}
