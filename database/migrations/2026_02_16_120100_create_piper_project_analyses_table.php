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
        Schema::create('piper_project_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();

            $table->json('source_conversation_ids')->nullable();
            $table->json('recurring_themes')->nullable();
            $table->json('repeated_goals')->nullable();
            $table->json('phases_mentioned')->nullable();
            $table->json('failure_modes_discussed')->nullable();
            $table->json('technical_constraints')->nullable();
            $table->json('model_routing_decisions')->nullable();

            $table->longText('expanded_context')->nullable();
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->index(['project_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piper_project_analyses');
    }
};
