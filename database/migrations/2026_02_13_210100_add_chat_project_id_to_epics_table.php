<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to add chat_project_id to epics table.
 *
 * This links epics (and their stories) to projects from
 * the ChatProjects application, enabling project-scoped
 * story management across the WritersRoom v1 system.
 */
return new class extends Migration
{
    /**
     * Add the chat_project_id column to epics table.
     *
     * Epics belong to a project, and stories belong to epics,
     * so this creates the project hierarchy.
     */
    public function up(): void
    {
        Schema::table('epics', function (Blueprint $table) {
            // Reference to project in ChatProjects database
            // Note: No foreign key constraint since it's cross-database
            $table->unsignedBigInteger('chat_project_id')
                ->nullable()
                ->after('id');

            // Index for efficient project-based queries
            $table->index('chat_project_id');
        });
    }

    /**
     * Remove the chat_project_id column.
     */
    public function down(): void
    {
        Schema::table('epics', function (Blueprint $table) {
            $table->dropIndex(['chat_project_id']);
            $table->dropColumn('chat_project_id');
        });
    }
};
