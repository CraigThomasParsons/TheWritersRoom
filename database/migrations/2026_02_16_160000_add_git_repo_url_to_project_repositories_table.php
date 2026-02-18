<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add per-repository Git link so each local code folder can map to its remote.
        if (! Schema::hasColumn('project_repositories', 'git_repo_url')) {
            Schema::table('project_repositories', function (Blueprint $table) {
                $table->string('git_repo_url', 500)->nullable()->after('path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove per-repository Git link if a rollback is requested.
        if (Schema::hasColumn('project_repositories', 'git_repo_url')) {
            Schema::table('project_repositories', function (Blueprint $table) {
                $table->dropColumn('git_repo_url');
            });
        }
    }
};
