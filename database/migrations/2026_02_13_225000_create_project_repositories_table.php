<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates project_repositories table for multi-repo projects.
     * A project can have many code folders/repositories.
     */
    public function up(): void
    {
        Schema::create('project_repositories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');                          // e.g., "TheWritersRoom"
            $table->string('path', 500);                     // e.g., "/home/craigpar/Code/TheWritersRoom"
            $table->string('type')->default('laravel');      // laravel, python, static, etc.
            $table->string('role')->nullable();              // writersroom, devbacklog, qaqueue, agent, docs
            $table->integer('display_order')->default(0);
            $table->boolean('is_primary')->default(false);   // Main repo for the project
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->index('project_id');
            $table->unique(['project_id', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_repositories');
    }
};
