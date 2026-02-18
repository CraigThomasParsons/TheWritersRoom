# PiperStoryArchitect API Contract

This contract supports the flow:

ChatProjects → PiperStoryArchitect → TheWritersRoom (Epics → Stories)

## Authentication

All Piper machine endpoints require `PIPER_TOKEN`.

Send one of:

- `Authorization: Bearer <token>`
- `X-Piper-Token: <token>`

---

## ChatProjects input endpoints

### 1) Get project + conversations input

`GET /api/projects/{project}/piper-input`

Returns:

- project metadata
- code context (`local_location`, `github_repo`, `gitea_location`, `framework_description`, `languages`)
- all saved conversations (`share_url` + manual paste)

### 2) Paste conversation manually

`POST /api/projects/{project}/conversations/paste`

Body:

```json
{
  "title": "optional",
  "raw_content": "full pasted transcript"
}
```

---

## WritersRoom output endpoints

### 1) Get project context for Piper

`GET /api/piper/projects/{project}/input`

Returns:

- project + code context
- chat conversations (if ChatProjects connection is available)
- latest analyses
- active personas

### 2) Store expanded analysis (not summary)

`POST /api/piper/projects/{project}/analysis`

Body example:

```json
{
  "source_conversation_ids": [12, 14],
  "recurring_themes": ["automation reliability"],
  "repeated_goals": ["reduce retries"],
  "phases_mentioned": ["sync", "compile", "verify"],
  "failure_modes_discussed": ["provider timeout"],
  "technical_constraints": ["must run in docker compose"],
  "model_routing_decisions": [
    {"stage": "analysis", "model": "chatgpt-5", "reason": "deep extraction"}
  ],
  "expanded_context": "Long-form expansion with details, edge cases, and rationale",
  "raw_payload": {"any": "extra data"}
}
```

### 3) Build epics and stories from Piper analysis

`POST /api/piper/projects/{project}/epics-stories`

Body example:

```json
{
  "analysis_id": 33,
  "epics": [
    {
      "title": "Reliability Foundation",
      "summary": "Stabilize ingest and retry behavior",
      "stories": [
        {
          "title": "Persist provider heartbeat",
          "narrative": "As an operator...",
          "acceptance_criteria": "...",
          "persona_key": "system_admin",
          "persona_name": "System Administrator",
          "priority": 100,
          "est_points": 3,
          "status_key": "draft"
        }
      ]
    }
  ]
}
```

Response includes summary totals and story identifiers:

```json
{
  "status": "ok",
  "project_id": 12,
  "summary": {
    "epics_created": 1,
    "epics_updated": 0,
    "stories_created": 3,
    "stories_updated": 0
  },
  "story_records": [
    {
      "story_id": 55,
      "story_title": "Persist provider heartbeat",
      "epic_id": 9,
      "epic_title": "Reliability Foundation",
      "was_created": true
    }
  ]
}
```

### 4) Store Piper comments on a story

`POST /api/piper/stories/{storyId}/comments`

Body example:

```json
{
  "message": "I drafted this story under the epic \"Reliability Foundation\".",
  "metadata": {
    "run_id": "b7c7f2c3-1eac-46a6-b210-4b02414a18d3",
    "analysis_id": 33
  }
}
```

---

## Required env vars

### ChatProjects

- `PIPER_TOKEN`

### TheWritersRoom

- `PIPER_TOKEN`
- `CHATPROJECTS_DB_HOST`
- `CHATPROJECTS_DB_PORT`
- `CHATPROJECTS_DB_DATABASE`
- `CHATPROJECTS_DB_USERNAME`
- `CHATPROJECTS_DB_PASSWORD`
