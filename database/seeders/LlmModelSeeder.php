<?php

namespace Database\Seeders;

use App\Models\LlmModel;
use Illuminate\Database\Seeder;

class LlmModelSeeder extends Seeder
{
    /**
     * Seed prioritized model catalog for Piper failover.
     */
    public function run(): void
    {
        $models = [
            ['provider' => 'gemini', 'model_key' => 'gemini-2.5-pro', 'display_name' => 'Gemini 2.5 Pro', 'priority' => 1],
            ['provider' => 'gemini', 'model_key' => 'gemini-2.5-flash', 'display_name' => 'Gemini 2.5 Flash', 'priority' => 2],
            ['provider' => 'anthropic', 'model_key' => 'claude-sonnet-4-5-20250929', 'display_name' => 'Claude Sonnet 4.5', 'priority' => 3],
            ['provider' => 'anthropic', 'model_key' => 'claude-haiku-4-5-20251001', 'display_name' => 'Claude Haiku 4.5', 'priority' => 4],
            ['provider' => 'openai', 'model_key' => 'gpt-5', 'display_name' => 'GPT-5', 'priority' => 5],
            ['provider' => 'openai', 'model_key' => 'gpt-4.1', 'display_name' => 'GPT-4.1', 'priority' => 6],
            ['provider' => 'openai', 'model_key' => 'gpt-4o', 'display_name' => 'GPT-4o', 'priority' => 7],
        ];

        foreach ($models as $model) {
            LlmModel::query()->updateOrCreate(
                [
                    'provider' => $model['provider'],
                    'model_key' => $model['model_key'],
                ],
                [
                    'display_name' => $model['display_name'],
                    'priority' => $model['priority'],
                    'is_enabled' => true,
                    'supports_code' => true,
                    'health_status' => 'unknown',
                ]
            );
        }
    }
}
