# LLM Model Catalog & Failover System

## Overview
PiperStoryArchitect now supports multiple LLM providers with automatic failover when quota/rate limits are hit.

## Configuration

### Database Setup
Run migrations and seeders to create the model catalog:
```bash
cd /home/craigpar/Code/TheWritersRoom
php artisan migrate
php artisan db:seed
```

### Provider Keys
Update `PiperStoryArchitect/config.json` with available provider API keys:
```json
{
  "openai_api_key": "sk-...",
  "gemini_api_key": "your-gemini-key-here"
}
```

Both keys are optional, but at least one is required. Piper will use the enabled models from WritersRoom in priority order.

## Model Management UI

Access the model management interface at:
**http://stories.elasticgun.com/llm-models**

Features:
- Reorder model priority (lower number = higher priority)
- Enable/disable individual models
- View health status

## Current Model Priority (Default)
1. GPT-5.3-Codex
2. GPT-5.2-Codex
3. GPT-5.2
4. Gemini 3 Pro
5. Claude 3.7 Sonnet
6. Claude 3.5 Sonnet
99. Grok Code Fast 1

## API Endpoint
Piper fetches the prioritized model list from:
```
GET /api/piper/llm-models
Authorization: Bearer <PIPER_TOKEN>
```

## Failover Behavior
When a provider returns:
- HTTP 429 (rate limit)
- Insufficient quota error
- Network timeout
- 5xx server error

Piper automatically tries the next enabled model in priority order.

## Adding New Models
To add new models, insert records into the `llm_models` table:
```sql
INSERT INTO llm_models (provider, model_key, display_name, priority, is_enabled, supports_code)
VALUES ('anthropic', 'claude-opus-4', 'Claude Opus 4', 10, true, true);
```

Or use the LlmModelSeeder in `TheWritersRoom/database/seeders/LlmModelSeeder.php`.

## Architecture Notes
- WritersRoom maintains the canonical model catalog
- Piper loads models on each run via API
- If API is unavailable, Piper falls back to config.json settings
- Health status tracking is planned for future updates
