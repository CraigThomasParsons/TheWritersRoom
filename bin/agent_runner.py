#!/usr/bin/env python3
import sys
import os
import json
import time
from dotenv import load_dotenv
import mysql.connector

# Load Laravel environment variables for DB access
env_path = os.path.join(os.path.dirname(os.path.dirname(__file__)), '.env')
load_dotenv(dotenv_path=env_path)

def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        user=os.getenv("DB_USERNAME", "root"),
        password=os.getenv("DB_PASSWORD", ""),
        database=os.getenv("DB_DATABASE", "chat_projects_db"),
        port=os.getenv("DB_PORT", "3306")
    )

def log_message(cursor, db, room_id, agent_name, role, content, metadata=None):
    query = """
    INSERT INTO messages (room_id, agent_name, role, content, metadata, created_at, updated_at) 
    VALUES (%s, %s, %s, %s, %s, NOW(), NOW())
    """
    meta_json = json.dumps(metadata) if metadata else None
    cursor.execute(query, (room_id, agent_name, role, content, meta_json))
    db.commit()

def fetch_run(cursor, run_id):
    cursor.execute("SELECT id, room_id, status FROM runs WHERE id = %s", (run_id,))
    return cursor.fetchone()

def update_run_status(cursor, db, run_id, status):
    cursor.execute("UPDATE runs SET status = %s, updated_at = NOW() WHERE id = %s", (status, run_id))
    db.commit()

# --- Mock LLM Interface ---
# Since we are isolating the pipeline loop, these will simulate standard conversational output.
# In production, these hook into `AskGroq` or `OpenAI`.
def execute_agent_turn(agent_name, role, context):
    time.sleep(2) # Simulate processing thinking time for the UI
    if agent_name == "Producer":
        return "Okay Team, let's lock in the core theme. The final act requires a decisive shift. Piper, can you synthesize the main conflict points for the narrative arc?"
    elif agent_name == "Piper":
        return """Understood, Producer. Based on current data and narrative flow, here is the **synthesis of the critical conflict drivers** for the final arc:

### Key Narrative Points
1. **Internal vs. External Conflict**: Character X's inner turmoil directly impacts external events.
2. **Alliance Fragility**: The crucial alliance between Factions A and B is fracturing due to ideological divergence.
3. **Stakes Escalation**: Failure in the mission now results not just in personal defeat, but systemic collapse.

*Analysis complete. Awaiting feedback.*"""
    return "..."

def main():
    if len(sys.argv) < 2:
        print("Usage: python3 agent_runner.py <run_id>")
        sys.exit(1)

    run_id = sys.argv[1]

    try:
        db = get_db_connection()
        cursor = db.cursor(dictionary=True)
    except Exception as e:
        print(f"Failed to connect to database: {e}")
        sys.exit(1)

    try:
        run = fetch_run(cursor, run_id)
        if not run:
            print(f"Run {run_id} not found.")
            sys.exit(1)

        room_id = run['room_id']
        update_run_status(cursor, db, run_id, 'running')
        print(f"Executing deterministic agent loop for Run {run_id} in Room {room_id}...")

        # Turn 1: Producer
        producer_reply = execute_agent_turn("Producer", "agent", "")
        log_message(cursor, db, room_id, "Producer", "agent", producer_reply)

        # Turn 2: Piper
        piper_reply = execute_agent_turn("Piper", "agent", producer_reply)
        log_message(cursor, db, room_id, "Piper", "agent", piper_reply)

        # Mark explicitly complete
        update_run_status(cursor, db, run_id, 'complete')
        print(f"Run {run_id} completely successfully.")

    except Exception as e:
        print(f"Fatal execution error: {e}")
        update_run_status(cursor, db, run_id, 'failed')
        
    finally:
        cursor.close()
        db.close()

if __name__ == "__main__":
    main()
