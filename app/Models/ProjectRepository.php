<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRepository extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'path',
        'type',
        'role',
        'display_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the project this repository belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope: Primary repositories only.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope: By role (writersroom, devbacklog, qaqueue, agent, docs).
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope: Laravel projects.
     */
    public function scopeLaravel($query)
    {
        return $query->where('type', 'laravel');
    }

    /**
     * Scope: Python projects.
     */
    public function scopePython($query)
    {
        return $query->where('type', 'python');
    }
}
