<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Defines the behavioral constraints and system-level directives of an AI intelligence node.
 * 
 * Agents exist dynamically inside the simulated environment and utilize the parameters
 * here to formulate specific responses and analytical artifacts based on their defined roles.
 */
class Agent extends Model
{
    /**
     * Bypasses strict Laravel field assignment policies to allow frictionless UI model generation.
     * 
     * @var array
     */
    protected $guarded = [];
}
