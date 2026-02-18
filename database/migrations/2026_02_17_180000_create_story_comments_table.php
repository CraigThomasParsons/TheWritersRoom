<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the story comments table for human + Piper notes.
     */
    public function up(): void
    {
        Schema::create('story_comments', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('story_id')->constrained('stories')->cascadeOnDelete();

            $table->string('author_name');
            $table->string('author_type');
            $table->longText('message');
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Speed up recent comment lookups per story.
            $table->index(['story_id', 'created_at']);
        });
    }

    /**
     * Drop the story comments table.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_comments');
    }
};
