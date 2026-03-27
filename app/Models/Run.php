<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Maintains the operational polling state of a single multi-agent pipeline burst execution.
 * 
 * Both the web UI and the Python daemon strictly observe this model to ascertain
 * the current real-time state of an ongoing analytical simulation block.
 */
class Run extends Model
{
    /**
     * Completely disables mass-assignment blocks to accelerate queue state transitions natively.
     * 
     * @var array
     */
    protected $guarded = [];
}
