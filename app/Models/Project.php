<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class Project extends Model
{
    /**
     * Cache projection column checks to avoid repeated schema introspection.
     *
     * @var array<string, bool>
     */
    private static array $projectionColumnCache = [];

    /**
     * The primary key is not auto-incrementing (synced from ChatProjects).
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
      *
      * These fields mirror ChatProjects project metadata,
      * including optional code-context fields used by Piper.
     */
    protected $fillable = [
        'id',
        'project_uuid',
        'name',
          // Keep `code_folder` for backward compatibility with legacy sync paths.
          'code_folder',
        'local_location',
        'github_repo',
        'gitea_location',
        'framework_description',
        'languages',
        'description',
        'synced_at',
        'source_updated_at',
        'last_synced_at',
        'sync_hash',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'synced_at' => 'datetime',
        'source_updated_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Scope active projects while tolerating pre-migration schemas.
     */
    public function scopeActiveProjection($query)
    {
        if (! static::hasProjectionColumn('deleted_at')) {
            return $query;
        }

        return $query->whereNull('deleted_at');
    }

    /**
     * Get the repositories belonging to this project.
      *
      * Repositories are ordered for predictable display in UI flows.
     */
    public function repositories(): HasMany
    {
        return $this->hasMany(ProjectRepository::class)->orderBy('display_order');
    }

    /**
     * Piper analysis records linked to this project.
      *
      * Newest first allows downstream tools to reuse the latest extraction.
     */
    public function piperAnalyses(): HasMany
    {
        return $this->hasMany(PiperProjectAnalysis::class)->latest();
    }

    /**
     * Get the primary repository for this project.
     */
    public function primaryRepository()
    {
        // Prefer explicitly marked primary repo for tool routing.
        return $this->repositories()->where('is_primary', true)->first();
    }

    /**
     * Get repositories by role.
     */
    public function repositoriesByRole(string $role)
    {
        // Keep role filtering centralized to avoid repeated query fragments.
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
        // Build a project-scoped story query via epic linkage.
        return Story::whereIn('epic_id', $this->epics()->pluck('id'));
    }

    /**
     * Get stories ready for development.
     */
    public function readyStories()
    {
        // Only include stories marked ready_for_dev to support planning lanes.
        return $this->stories()
            ->whereHas('status', fn ($statusQuery) => $statusQuery->where('key', 'ready_for_dev'));
    }

    /**
     * Sync from ChatProjects data.
     *
     * This is an upsert so repeated sync runs remain idempotent.
     */
    public static function syncFromChatProjects(array $data): self
    {
        $result = static::syncFromRegistry($data);
        return $result['project'];
    }

    /**
     * Upsert projection data while ignoring stale source updates.
     *
     * @return array{project: self, stale: bool}
     */
    public static function syncFromRegistry(array $payload): array
    {
        $sourceUpdatedAt = isset($payload['source_updated_at']) && $payload['source_updated_at']
            ? Carbon::parse((string) $payload['source_updated_at'])
            : null;

        $project = static::query()->find((int) $payload['id']);

        if ($project === null) {
            $project = new static();
            $project->id = (int) $payload['id'];
        }

        // Preserve existing values when webhook payloads intentionally omit unchanged fields.
        $resolvedName = $payload['name'] ?? $project->name ?? ('Project ' . $payload['id']);

        // Guard clause: reject stale source updates while still recording heartbeat.
        if (
            $project->exists
            && $sourceUpdatedAt !== null
            && static::hasProjectionColumn('source_updated_at')
            && $project->source_updated_at !== null
            && $sourceUpdatedAt->lt($project->source_updated_at)
        ) {
            if (static::hasProjectionColumn('last_synced_at')) {
                $project->forceFill([
                    'last_synced_at' => now(),
                ])->save();
            }

            return [
                'project' => $project,
                'stale' => true,
            ];
        }

        // Resolve full effective payload before hashing so partial payloads hash correctly.
        $resolvedPayload = [
            'id' => $payload['id'],
            'project_uuid' => $payload['project_uuid'] ?? $project->project_uuid,
            'name' => $resolvedName,
            'description' => $payload['description'] ?? $project->description,
            'code_folder' => $payload['code_folder'] ?? $project->code_folder,
            'local_location' => $payload['local_location'] ?? $project->local_location,
            'github_repo' => $payload['github_repo'] ?? $project->github_repo,
            'gitea_location' => $payload['gitea_location'] ?? $project->gitea_location,
            'framework_description' => $payload['framework_description'] ?? $project->framework_description,
            'languages' => $payload['languages'] ?? $project->languages,
        ];

        if (static::hasProjectionColumn('deleted_at')) {
            $resolvedPayload['deleted_at'] = $payload['deleted_at'] ?? $project->deleted_at?->toISOString();
        }

        // Compute deterministic hash from canonical fields for drift diagnostics.
        $syncHash = hash('sha256', json_encode($resolvedPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $projectUpdatePayload = [
            'project_uuid' => $payload['project_uuid'] ?? $project->project_uuid,
            'name' => $resolvedName,
            'code_folder' => $payload['code_folder'] ?? $project->code_folder,
            'local_location' => $payload['local_location'] ?? $project->local_location,
            'github_repo' => $payload['github_repo'] ?? $project->github_repo,
            'gitea_location' => $payload['gitea_location'] ?? $project->gitea_location,
            'framework_description' => $payload['framework_description'] ?? $project->framework_description,
            'languages' => $payload['languages'] ?? $project->languages,
            'description' => $payload['description'] ?? $project->description,
        ];

        if (static::hasProjectionColumn('synced_at')) {
            $projectUpdatePayload['synced_at'] = now();
        }

        if (static::hasProjectionColumn('last_synced_at')) {
            $projectUpdatePayload['last_synced_at'] = now();
        }

        if (static::hasProjectionColumn('source_updated_at')) {
            $projectUpdatePayload['source_updated_at'] = $sourceUpdatedAt;
        }

        if (static::hasProjectionColumn('sync_hash')) {
            $projectUpdatePayload['sync_hash'] = $syncHash;
        }

        if (static::hasProjectionColumn('deleted_at')) {
            $projectUpdatePayload['deleted_at'] = isset($payload['deleted_at']) && $payload['deleted_at']
                ? Carbon::parse((string) $payload['deleted_at'])
                : null;
        }

        $project->forceFill($projectUpdatePayload)->save();

        return [
            'project' => $project,
            'stale' => false,
        ];
    }

    /**
     * Check whether a projection column exists in the current database schema.
     */
    private static function hasProjectionColumn(string $column): bool
    {
        if (array_key_exists($column, static::$projectionColumnCache)) {
            return static::$projectionColumnCache[$column];
        }

        $tableName = (new static())->getTable();

        static::$projectionColumnCache[$column] = Schema::hasColumn($tableName, $column);

        return static::$projectionColumnCache[$column];
    }
}
