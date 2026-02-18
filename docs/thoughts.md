# Thoughts Log: Projects → Piper → WritersRoom

## 2026-02-17: First successful end-to-end run

### What was the test

- Source: `https://projects.elasticgun.com/projects/4/conversations/7`
- Goal:
  1. Sync source context into WritersRoom via Piper.
  2. Generate analysis and stories from conversation 7 specifically.
  3. If phases are discovered, split them into separate epics.

### What happened (chronological)

#### Unblocking runtime

1. **PIPER_TOKEN was empty** — discovered `php artisan piper:token` exists in ChatProjects. Generated token and synced to TheWritersRoom `.env` and PiperStoryArchitect `config.json`.

2. **`deleted_at` column missing** — ChatProjects Project model uses `SoftDeletes` but no migration added the column. Created and ran migration.

3. **TheWritersRoom DB connection** — `.env` had `DB_HOST=db` which is a Docker hostname. Initially tried switching to PostgreSQL (wrong approach). Discovered both TheWritersRoom and TheDevBacklog run as Docker containers with their own MySQL. Reverted `.env` and ran `docker exec thewritersroom_app php artisan migrate:fresh --seed` inside the container.

4. **LLM model catalog had fake model names** — Seeder had `gpt-5.3-codex`, `gpt-5.2-codex` etc. which don't exist. Updated to real models: `gpt-5`, `gpt-4.1`, `gpt-4o`, `gemini-2.5-pro`, `gemini-2.5-flash`.

5. **OpenAI quota exhausted** — All OpenAI models returned `insufficient_quota`. Added Gemini API key as fallback. Gemini Flash handled both extraction and generation successfully.

#### First run result: 8 phases → 1 epic (bad)

The LLM returned all stories under a single generic epic. The post-processing `_enforcePhaseSeparatedEpics` tried to redistribute stories by keyword matching, but none matched, so all 11 stories fell into the first bucket.

**Root cause**: The LLM prompt was too weak — "If analysis includes multiple phases, produce one epic per phase" was treated as a suggestion, not a requirement.

#### Fixes applied

1. **Improved analysis prompt** — Added guidance that `phases_mentioned` should only include implementation phases/milestones, not workflow steps (brainstorming, planning, execution, reflection are NOT phases).

2. **Improved epic/story generation prompt** — Major rewrite:
   - System message now specifies narrative format ("As a [persona], I need...") and acceptance criteria format ("- [ ]" checklists).
   - When phases exist, injects a `CRITICAL` instruction with exact phase names as required epic titles.
   - Schema annotations describe expected format for each field.
   - Rules are much more explicit about fibonacci estimation, draft status, minimum stories per epic.

3. **Auto-create projects in WritersRoom** — Changed PiperController from route model binding (`Project $project`) to raw `int $projectId` with `firstOrCreate`. This means Piper can write analysis/stories even before the project exists in WritersRoom.

4. **Fixed StoryController** — Was eager-loading a `sprint` (singular) relationship that didn't exist. The model has `sprints()` (BelongsToMany through pivot). Fixed all references.

#### Second run result: 5 phases → 5 epics, 16 stories (good)

- Phases: Sprint 0, Sprint 1, Phase 1, Phase 2, Phase 3
- Each epic has 2-4 stories
- Stories have proper persona-based narratives
- Acceptance criteria are concrete checklists
- Fibonacci point estimates applied
- All stories start as draft

### Key learnings

1. **Docker awareness is critical** — These apps run in Docker. `.env` changes on disk are picked up by the containers (volume mount), but `DB_HOST=db` is correct — it's a Docker network hostname. Never change it to `127.0.0.1`.

2. **LLM prompts must be authoritative, not suggestive** — "If X then Y" gets ignored. "CRITICAL: You MUST do X" with concrete examples works. Injecting the actual phase names as required epic titles was the key fix.

3. **Post-processing is a safety net, not the primary mechanism** — The `_enforcePhaseSeparatedEpics` fallback should rarely fire. If it does, the prompt needs fixing, not the post-processor.

4. **Seeders matter after migrate:fresh** — Status lookup tables (epic_statuses, story_statuses, sprint_statuses) and LLM models must be seeded. The `buildEpicsAndStories` endpoint returns 422 without them.

5. **Route model binding vs auto-create** — For machine-to-machine APIs where the caller creates data, route model binding causes unnecessary 404s. Using raw IDs with `firstOrCreate` is more resilient.

### Files changed

| File | Change |
|------|--------|
| `PiperStoryArchitect/bin/piper_story_architect.py` | Improved analysis + generation prompts |
| `PiperStoryArchitect/config.json` | Added gemini_api_key, updated piper_token |
| `TheWritersRoom/app/Http/Controllers/Api/PiperController.php` | Route model binding → raw int + firstOrCreate |
| `TheWritersRoom/app/Http/Controllers/Api/StoryController.php` | Fixed `sprint` → `sprints` relation |
| `TheWritersRoom/routes/api.php` | `{project}` → `{projectId}` for Piper routes |
| `TheWritersRoom/database/seeders/LlmModelSeeder.php` | Real model names |
| `ChatProjects/database/migrations/*_add_soft_deletes_to_projects_table.php` | New migration |
