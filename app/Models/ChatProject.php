<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ChatProject model reads from the ChatProjects database.
 *
 * This model connects to the external chatprojects database
 * to access project information like name, description, and
 * the code_folder path for where edits should be made.
 */
class ChatProject extends Model
{
    /**
     * The connection name for this model.
     *
     * Points to the chatprojects database configured in database.php.
     */
    protected $connection = 'chatprojects';

    /**
     * The table associated with the model.
     *
     * Maps to the projects table in the chatprojects database.
     */
    protected $table = 'projects';

    /**
     * The attributes that are mass assignable.
     *
     * Note: This model is read-only from TheWritersRoom.
     * Project management is done in ChatProjects app.
     */
    protected $fillable = ['name', 'description', 'code_folder'];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;
}
