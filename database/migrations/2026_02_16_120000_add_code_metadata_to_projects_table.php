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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('local_location')->nullable()->after('code_folder');
            $table->string('github_repo')->nullable()->after('local_location');
            $table->string('gitea_location')->nullable()->after('github_repo');
            $table->text('framework_description')->nullable()->after('gitea_location');
            $table->text('languages')->nullable()->after('framework_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'local_location',
                'github_repo',
                'gitea_location',
                'framework_description',
                'languages',
            ]);
        });
    }
};
