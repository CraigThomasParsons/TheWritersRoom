<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a distinct physical partition where intelligent agents converse.
 * 
 * This model isolates message streams and execution trackers to prevent
 * context corruption across different analytical environments.
 */
class Room extends Model
{
    /**
     * Unlocks mass-assignment arrays for dynamic instantiation.
     * 
     * @var array
     */
    protected $guarded = [];
}
