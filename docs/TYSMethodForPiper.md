# TYS Method for Piper Project→Sprint Pipeline

This is the execution method Piper follows for any feature, fix, or behavior change in this pipeline:

Projects (ChatProjects) → PiperStoryArchitect → TheWritersRoom (Epics + Stories)

## Loop

1. **Pick one feature/fix** (single, scoped change).
2. **Implement only that change** (no extra refactors).
3. **Test that exact change** (smallest test that proves it works).
4. **Decide**:
   - Yes: proceed to next feature/fix.
   - No: fix and re-test the same change.
5. **Document** in `docs/thoughts.md`:
   - What was intended
   - What happened
   - What was changed
6. **Repeat** with the next single change.

## Preflight (before any run)

```bash
python3 bin/piper_story_architect.py --config config.json preflight --project-id <id>
```

Both endpoints must return 200 or non-blocking status. Fix any auth/schema issues before proceeding.

## Provider Health Check (before any run)

```bash
python3 bin/piper_story_architect.py --config config.json provider-health
```

Each enabled provider is probed with a minimal JSON request. Fix credentials or endpoints if any probe fails.

## Full Run

```bash
python3 bin/piper_story_architect.py --config config.json run-project --project-id <id> --conversation-id <id>
```

## Required quality gates per loop

### 1. Analysis extraction
- Phases must be implementation milestones, not workflow steps (brainstorming/planning/execution are NOT phases).
- Each phase should represent a buildable increment.
- Technical constraints and failure modes must be preserved at full depth.

### 2. Epic/Story generation
- One epic per extracted phase (when multiple phases exist).
- Every epic must have at least 2 stories.
- Stories must follow: "As a [persona], I need [capability], so that [benefit]."
- Acceptance criteria must be `- [ ]` markdown checklists with independently testable items.
- Point estimates use fibonacci (1, 2, 3, 5, 8, 13).
- All new stories start as `draft` status.

### 3. Persistence
- Epics/stories created or updated through WritersRoom API, never manual insertion.
- Project auto-created in WritersRoom on first analysis write (no need for pre-sync).
- Re-runs are idempotent (upsert by project+title for epics, epic+title for stories).

## Non-negotiable rules

- Piper is the only author of generated epics/stories in this flow.
- Humans do not manually create epics/stories to bypass failed generation.
- Stale or partial runs must be re-run from source conversation scope.
- If generation quality drops, improve Piper prompt/logic and re-test.

## LLM Failover

Piper tries models in priority order from TheWritersRoom's LLM catalog:
1. OpenAI models (Codex-capable, e.g. gpt-5, gpt-4.1, gpt-4o)
2. Anthropic models (Claude Sonnet class)
3. Gemini models (gemini-2.5-pro, gemini-2.5-flash)
4. Grok Code Fast (second-to-last fallback, not yet implemented)
5. Goose + Local LLMs (last resort, not yet implemented)

Rate limits, quota errors, and 5xx trigger failover to next candidate.

Provider notes:
- Grok Code Fast requires `xai_api_key` (PiperStoryArchitect config).
- Goose + Local LLMs require `goose_base_url` and/or `local_llm_base_url`.

## Clear execution method (Piper behavior rule)

1. **Build one behavior** (no batching multiple features).
2. **Run a targeted test** that proves the behavior works.
3. **If it fails**, fix the smallest root cause and re-run the same test.
4. **If it passes**, move to the next behavior.
5. **Log the work** in `docs/thoughts.md` with intent + result + changes.

## Regression checks

- Project 4 + Conversation 7 runs end-to-end through Piper.
- Analysis is stored in `piper_project_analyses`.
- Generated output includes separate epics when phases are present.
- stories.elasticgun.com loads without errors.
- No direct DB write scripts are used for story authoring.

## Infrastructure notes

- TheWritersRoom and TheDevBacklog run as Docker containers with MySQL.
- `DB_HOST=db` in `.env` is correct (Docker network hostname).
- Migrations and seeders must run inside Docker: `docker exec thewritersroom_app php artisan migrate`
- PIPER_TOKEN is generated via `php artisan piper:token` in ChatProjects and must be synced to TheWritersRoom `.env` and PiperStoryArchitect `config.json`.
