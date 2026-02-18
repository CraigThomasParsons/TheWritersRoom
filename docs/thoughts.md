# Thoughts Log: Projects → Piper → WritersRoom

## 2026-02-18: Shared story comments + Piper thought delivery

### Why

- Piper should only do what a human can already do in the UI.
- We needed a shared comment stream so Piper thoughts appear as ticket comments.

### What changed

1. **Story comments table + model** — Added `story_comments` with `author_name`, `author_type`, `message`, and optional `metadata`.
2. **Human comment UI** — Story show page now includes a comment form and lists comments in reverse chronological order.
3. **Piper API comment endpoint** — Added `POST /api/piper/stories/{storyId}/comments` so Piper can write into the same comment stream.
4. **Story ID echo in generation response** — `POST /api/piper/projects/{projectId}/epics-stories` now returns `story_records` so Piper can attach comments to the correct tickets.
5. **Documentation updates** — Updated `piper_story_architect_api.md` and `TYSMethodForPiper.md` (LLM failover).

### Files touched

| File | Change |
| --- | --- |
| `TheWritersRoom/database/migrations/2026_02_17_180000_create_story_comments_table.php` | New story comments table |
| `TheWritersRoom/app/Models/StoryComment.php` | New model |
| `TheWritersRoom/app/Models/Story.php` | `comments()` relationship |
| `TheWritersRoom/app/Http/Controllers/StoryController.php` | Load + store human comments |
| `TheWritersRoom/resources/views/stories/show.blade.php` | Comment form + list |
| `TheWritersRoom/app/Http/Controllers/Api/PiperController.php` | Piper comment endpoint + `story_records` response |
| `TheWritersRoom/routes/api.php` | Piper comments route |
| `TheWritersRoom/routes/web.php` | Story comments route |
| `TheWritersRoom/docs/piper_story_architect_api.md` | Response + comment endpoint |
| `TheWritersRoom/docs/TYSMethodForPiper.md` | LLM failover list |

## 2026-02-18: LLM catalog expanded for Grok + Goose/Local

### Why

- Implemented the Grok Code Fast and Goose/Local fallbacks, so the catalog needs entries.

### What changed

1. **LLM catalog seed update** — Added xAI Grok Code Fast, Goose Local, and Local LLM models (disabled by default).

### Files touched

| File | Change |
| --- | --- |
| `TheWritersRoom/database/seeders/LlmModelSeeder.php` | Added xAI + Goose + Local models |

## 2026-02-18: Added provider health check command

### Why

- Needed a fast, explicit probe for enabled models before full runs.

### What changed

1. **Provider health command** — `provider-health` now probes each enabled model candidate.
2. **Docs updated** — TYSMethod includes the provider health step.

### What happened during testing

- Provider health check failed for OpenAI and Gemini Pro due to quota limits.
- Gemini Flash responded successfully, so it remains the only viable provider for now.

## 2026-02-18: Project 4 / Conversation 7 run (Piper)

### What happened

- Preflight succeeded for ChatProjects and TheWritersRoom.
- Piper generated epics and stories successfully via Gemini Flash.
- Piper failed to write comments because `story_comments` table is missing in the container.

### Action needed

- Run `php artisan migrate` inside the WritersRoom container, then re-run Piper to publish comments.

### Follow-up

- Migration was applied, but the next run failed on another malformed JSON response.
- Added a brace-balanced JSON extractor to Piper to harden parsing.

### Latest run

- After migration + JSON fix, Piper completed the run and published story comments successfully.

## 2026-02-18: Epic "Ready for Dev" button

### Why

- Epic drafts in DevBacklog only appear after a sync.
- Writers need a one-click way to mark an epic ready and push it to DevBacklog.

### What changed

1. **Ready for Dev action** — Epic view now has a button that marks all child stories as ready.
2. **DevBacklog sync trigger** — WritersRoom calls a DevBacklog API endpoint to sync ready stories.
3. **Sync endpoint** — DevBacklog exposes `POST /api/stories/sync-ready` to run `ccdf:sync-stories`.

### Config required

- WritersRoom `.env`: `DEVBACKLOG_BASE_URL`, `DEVBACKLOG_SYNC_TOKEN`
- DevBacklog `.env`: `DEVBACKLOG_SYNC_TOKEN`

### Files touched

| File | Change |
| --- | --- |
| `TheWritersRoom/app/Http/Controllers/EpicController.php` | Ready-for-dev action + sync call |
| `TheWritersRoom/resources/views/epics/show.blade.php` | Ready for Dev button |
| `TheWritersRoom/routes/web.php` | Ready-for-dev route |
| `TheWritersRoom/config/services.php` | DevBacklog config |
| `TheDevBacklog/app/Http/Controllers/Api/StoryController.php` | Sync-ready endpoint |
| `TheDevBacklog/routes/api.php` | Sync-ready route |
| `TheDevBacklog/config/services.php` | Sync token config |

### Bug fix after first live run

- DevBacklog sync crashed with `Attempt to read property "id" on null` in `SyncStoriesCommand::syncEpic`.
- Root cause: local `epic_statuses` can be missing, and sync code dereferenced a null status.
- Fix: added guard clause in sync command to fail with a clear message when epic statuses are missing, and passed a resolved status id into `syncEpic()` to avoid null dereference.

### Bug fix after duplicate epic report

- DevBacklog Epic Drafts showed duplicate rows for the same epic title/project.
- Root cause: story sync could create parallel epic rows over time and never reconcile duplicates.
- Fix: changed epic sync matching to prefer `(chat_project_id, title)` and added a consolidation pass that re-links stories to one canonical epic and deletes duplicates.
- Validation: running `ccdf:sync-stories --project=4` reported `Consolidated 5 duplicate epic record(s)` and the epic drafts page rendered one row for the affected epic.

### Bug fix after sprint visibility report

- Moving an epic draft to sprint could appear "missing" from Current Sprint and filtered Sprint views.
- Root cause: move flow created new sprints in `draft` status, while Current Sprint prioritizes `active` and users often filter Sprints by active.
- Fix: `Move to Sprint` now creates sprints with status precedence `active -> ready -> draft`.

### Bug fix after sprint update/delete regression

- In DevBacklog, clicking `Update Sprint` could delete the sprint.
- Root cause: `sprints/edit.blade.php` had nested forms (update form wrapping delete form), causing invalid HTML form submission behavior.
- Fix: separated delete action into its own form (`sprint-delete-form`) and wired delete button using `form=` attribute.

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
