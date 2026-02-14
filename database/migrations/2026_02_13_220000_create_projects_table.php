<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the projects table (synced from ChatProjects).
     * The epics table already has chat_project_id - we add a foreign key
     * now that we have a local projects table.
     */
    public function up(): void
    {
        // Create projects table (projection from ChatProjects)
        Schema::create('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // Same ID as ChatProjects
            $table->string('name');
            $table->string('code_folder', 500)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        // Add foreign key from epics.chat_project_id to projects.id
        // Note: chat_project_id column already exists from previous migration
        Schema::table('epics', function (Blueprint $table) {
            $table->foreign('chat_project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('epics', function (Blueprint $table) {
            $table->dropForeign(['chat_project_id']);
        });

        Schema::dropIfExists('projects');
    }
};
