# TheWritersRoom

Product Domain for the ElasticGun Studio. TheWritersRoom is the planning layer of the system. It is where ideas become structured Stories and where Sprints are defined with a single, explicit goal.

## Overview

TheWritersRoom is a Laravel 11 application that provides a product planning system with the following entities:
- **Epics**: High-level initiatives or themes
- **Stories**: Specific work items that belong to Epics and can be assigned to Sprints
- **Sprints**: Time-boxed iterations with a single goal that contain multiple Stories

## Key Features

- ✅ Full CRUD operations for Epics, Stories, and Sprints via REST API
- ✅ Sprint immutability: Once a Sprint is marked as "ready", it becomes immutable
- ✅ Model relationships: Sprint has many Stories, Story belongs to Sprint and Epic
- ✅ Event system: SprintCreated and SprintReady events are dispatched
- ✅ Comprehensive test coverage

## Installation

1. Clone the repository:
```bash
git clone https://github.com/CraigThomasParsons/TheWritersRoom.git
cd TheWritersRoom
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Create SQLite database:
```bash
touch database/database.sqlite
```

6. Run migrations:
```bash
php artisan migrate
```

7. Start the development server:
```bash
php artisan serve
```

## API Endpoints

### Epics

#### List all Epics
```http
GET /api/epics
```

#### Create an Epic
```http
POST /api/epics
Content-Type: application/json

{
  "title": "User Authentication Epic",
  "description": "Implement user authentication system"
}
```

#### Get a specific Epic
```http
GET /api/epics/{id}
```

#### Update an Epic
```http
PUT /api/epics/{id}
Content-Type: application/json

{
  "title": "Updated Epic Title",
  "description": "Updated description"
}
```

#### Delete an Epic
```http
DELETE /api/epics/{id}
```

### Stories

#### List all Stories
```http
GET /api/stories
```

#### Create a Story
```http
POST /api/stories
Content-Type: application/json

{
  "title": "Implement login page",
  "description": "Create a login page with email and password fields",
  "epic_id": 1,
  "sprint_id": 1
}
```

#### Get a specific Story
```http
GET /api/stories/{id}
```

#### Update a Story
```http
PUT /api/stories/{id}
Content-Type: application/json

{
  "title": "Updated Story Title",
  "description": "Updated description",
  "epic_id": 1,
  "sprint_id": 2
}
```

#### Delete a Story
```http
DELETE /api/stories/{id}
```

### Sprints

#### List all Sprints
```http
GET /api/sprints
```

#### Create a Sprint
```http
POST /api/sprints
Content-Type: application/json

{
  "title": "Sprint 1",
  "goal": "Complete user authentication features",
  "status": "draft"
}
```

**Note**: SprintCreated event is dispatched upon creation.

#### Get a specific Sprint
```http
GET /api/sprints/{id}
```

#### Update a Sprint
```http
PUT /api/sprints/{id}
Content-Type: application/json

{
  "title": "Updated Sprint Title",
  "goal": "Updated goal",
  "status": "ready"
}
```

**Note**: 
- When status changes to "ready", SprintReady event is dispatched
- Once a Sprint is marked as "ready", it becomes immutable and cannot be updated

#### Delete a Sprint
```http
DELETE /api/sprints/{id}
```

## Sprint Status

A Sprint can have one of three statuses:
- `draft`: Initial state, Sprint can be freely modified
- `ready`: Sprint is finalized and becomes immutable
- `closed`: Sprint is completed

## Data Model

### Epic
- `id`: Integer (primary key)
- `title`: String
- `description`: Text (nullable)
- `created_at`: Timestamp
- `updated_at`: Timestamp

### Story
- `id`: Integer (primary key)
- `title`: String
- `description`: Text (nullable)
- `epic_id`: Foreign key to Epic (nullable)
- `sprint_id`: Foreign key to Sprint (nullable)
- `created_at`: Timestamp
- `updated_at`: Timestamp

### Sprint
- `id`: Integer (primary key)
- `title`: String
- `goal`: Text (required)
- `status`: Enum (draft, ready, closed)
- `created_at`: Timestamp
- `updated_at`: Timestamp

## Events

### SprintCreated
Dispatched when a new Sprint is created.

### SprintReady
Dispatched when a Sprint's status changes to "ready".

## Testing

Run the test suite:
```bash
php artisan test
```

Current test coverage:
- 22 tests
- 48 assertions
- 100% passing

## License

See [LICENSE](LICENSE) file for details.
