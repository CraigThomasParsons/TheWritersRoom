<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds projection-hardening metadata so project sync can:
     * - use stable UUID identity from the canonical registry,
     * - reject stale updates based on source timestamps,
     * - detect payload drift via hash,
     * - support soft-deleted upstream projects.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->uuid('project_uuid')->nullable()->after('id');
            $table->timestamp('source_updated_at')->nullable()->after('synced_at');
            $table->timestamp('last_synced_at')->nullable()->after('source_updated_at');
            $table->string('sync_hash', 64)->nullable()->after('last_synced_at');
            $table->softDeletes();

            $table->unique('project_uuid');
            $table->index('source_updated_at');
            $table->index('last_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['project_uuid']);
            $table->dropIndex(['source_updated_at']);
            $table->dropIndex(['last_synced_at']);
            $table->dropSoftDeletes();

            $table->dropColumn([
                'project_uuid',
                'source_updated_at',
                'last_synced_at',
                'sync_hash',
            ]);
        });
    }
};
