# Task Kitty

Task Kitty is an enterprise task and workforce management application built with Laravel and React. It combines project management (Kanban boards, subtasks, dependencies), employee attendance tracking, multi-tier leave approvals, and HR reporting in a single Inertia-powered SPA.

## Features

### Project & task management
- Projects with Kanban boards (`todo` → `in_progress` → `review` → `done`)
- Drag-and-drop task moves with optimistic UI updates
- Subtasks, comments, tags, and file attachments (Spatie Media Library)
- Task dependencies (finish-to-start) with validation before status changes
- Time logging with estimated vs. actual minutes
- Real-time Kanban updates via Laravel Reverb

### Attendance
- Clock in / clock out with pause and resume support
- Segment-based hours and overtime calculation
- Optional geofence validation against office locations
- Browser fingerprint and IP logging for audit trails

### Leave management
- Multi-tier approval workflow: Team Lead → HR Admin
- Employee self-service leave requests
- Approver queue with tier-specific actions and denial comments

### HR & reporting
- HR dashboard with daily attendance stats and filterable employee records
- Employee daily work report
- Role-based access control with five enterprise roles

### Platform
- Authentication via Laravel Breeze (email verification, password reset)
- Activity logging (Spatie Activity Log)
- API resources for consistent JSON payloads
- WebSocket broadcasting with connection status indicator

## Tech stack

| Layer | Technologies |
|-------|--------------|
| Backend | PHP 8.2, Laravel 12 |
| Frontend | React 18, Inertia.js, Tailwind CSS, Vite |
| Real-time | Laravel Reverb, Laravel Echo, Pusher JS |
| Auth & RBAC | Laravel Breeze, Spatie Laravel Permission |
| Media & audit | Spatie Media Library, Spatie Activity Log |

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ and npm
- MySQL (or SQLite for local/testing)
- Optional: Redis (production caching/queues), Laravel Reverb (real-time)

## Installation

1. **Clone the repository** and enter the project directory.

2. **Install PHP dependencies:**

   ```bash
   composer install
   ```

3. **Install frontend dependencies:**

   ```bash
   npm install
   ```

4. **Configure the environment:**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Update `.env` with your database credentials. The default database name is `task_kitty`.

5. **Run migrations and seed demo data:**

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

   For a clean slate:

   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Build frontend assets** (production):

   ```bash
   npm run build
   ```

## Development

Start the full local stack (HTTP server, queue worker, log tail, Reverb, and Vite):

```bash
composer dev
```

On Windows, or when you do not need WebSockets:

```bash
composer dev:no-reverb
```

This runs:
- `php artisan serve` — application server
- `php artisan queue:listen` — queue worker
- `php artisan pail` — log viewer
- `php artisan reverb:start` — WebSocket server (full stack only)
- `npm run dev` — Vite dev server with HMR

Visit [http://localhost:8000](http://localhost:8000).

## Demo accounts

After seeding, use these accounts (password: `password`):

| Role | Email |
|------|-------|
| Super Admin | `superadmin@taskkitty.test` |
| HR Admin | `hr@taskkitty.test` |
| Project Manager | `pm@taskkitty.test` |
| Team Lead | `lead@taskkitty.test` |
| Employee | `employee@taskkitty.test` |

## Roles & permissions

| Role | Capabilities |
|------|--------------|
| **Super Admin** | Full access, HR dashboard, leave approval |
| **HR Admin** | HR dashboard, final leave approval |
| **Project Manager** | Create projects, edit any task |
| **Team Lead** | First-tier leave approval |
| **Employee** | Clock in/out, submit leave, manage assigned tasks |

## Main routes

| Path | Description |
|------|-------------|
| `/dashboard` | Employee dashboard with attendance clock panel |
| `/projects` | Project list |
| `/projects/{id}` | Kanban board for a project |
| `/tasks/{id}` | Task detail view |
| `/leave-requests` | Leave requests and approval queue |
| `/hr` | HR attendance dashboard (HR Admin, Super Admin) |
| `/work-report` | Employee daily work report |

## Real-time broadcasting

Real-time updates are disabled by default (`BROADCAST_CONNECTION=log`). To enable Reverb locally:

1. Set in `.env`:

   ```env
   BROADCAST_CONNECTION=reverb
   ```

   Ensure `REVERB_*` and `VITE_REVERB_*` variables match `.env.example`.

2. Start Reverb (included in `composer dev`, or separately):

   ```bash
   php artisan reverb:start
   ```

3. Rebuild or run the Vite dev server so frontend env vars are picked up.

Broadcast channels:

| Event | Channel |
|-------|---------|
| Task created / status updated | `private-projects.{id}` |
| Attendance updated | `private-users.{id}` |
| Leave request updated | `private-leave-requests` |

## Configuration

Key environment variables (see `.env.example` for the full list):

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_TIMEZONE` | `Asia/Manila` | Application timezone |
| `ATTENDANCE_STANDARD_MINUTES` | `480` | Standard work day length (8 hours) |
| `ATTENDANCE_REQUIRE_GEOFENCE` | `false` | Require GPS geofence on clock-in (set `true` in production) |
| `QUEUE_CONNECTION` | `database` | Use `redis` in production when available |
| `REDIS_ENABLED` | `false` | Enable Redis-backed cache and queues |

Enterprise-specific settings live in `config/enterprise.php`.

## Testing

Tests use an in-memory SQLite database (configured in `phpunit.xml`).

```bash
php artisan test
```

Or run PHPUnit directly:

```bash
./vendor/bin/phpunit
```

Test suites cover attendance calculation, leave approval flows, task dependencies, broadcasting, HR dashboard, and authorization.

## Project structure

```
app/
├── Enums/              # TaskStatus, EnterpriseRole, AttendanceStatus, etc.
├── Events/             # Broadcast events (Tasks, Leave, Attendance)
├── Http/
│   ├── Controllers/    # Thin controllers delegating to services
│   ├── Requests/       # Form request validation
│   └── Resources/      # API resources for Inertia & broadcasts
├── Models/             # Eloquent models with relationships
├── Policies/           # Authorization policies
└── Services/           # Business logic (Attendance, Leave, Tasks, HR, Reports)

resources/js/
├── Components/         # React components (Kanban, modals, UI primitives)
├── hooks/              # Echo channel hooks, connection status
├── Layouts/            # AppShell, AuthenticatedLayout
├── Pages/              # Inertia page components
└── stores/             # Zustand client state

docs/                   # Enterprise upgrade step-by-step documentation
```

## Architecture notes

- **Controllers** are thin; business logic lives in final service/action classes under `app/Services/`.
- **Policies** gate authorization; Spatie roles map to `EnterpriseRole` enum values.
- **Activity logging** is applied via `LogsActivityTrait` on key models (`Task`, `Attendance`, `LeaveRequest`).
- **Exceptions** such as `GeofenceViolationException` and `TaskDependencyBlockedException` render as Inertia flash messages.

## Further documentation

Detailed implementation notes for the enterprise upgrade are in the `docs/` folder:

- `docs/ENTERPRISE_STEP1.md` — Packages, layout, and infrastructure
- `docs/ENTERPRISE_STEP2.md` — Database schema and seeders
- `docs/ENTERPRISE_STEP3.md` — Service layer and actions
- `docs/ENTERPRISE_STEP4.md` — Broadcasting and API resources
- `docs/ENTERPRISE_STEP5.md` — HR dashboard and leave UI
- `docs/ENTERPRISE_STEP6.md` — Optimistic Kanban and reconnection UX

## Production checklist

- Set `APP_ENV=production`, `APP_DEBUG=false`
- Run `php artisan config:cache`, `route:cache`, and `view:cache`
- Run `npm run build` and serve via a proper web server (Nginx/Apache)
- Use `BROADCAST_CONNECTION=reverb` with a running Reverb server
- Run `php artisan queue:listen` or deploy a queue worker (Horizon on Linux)
- Configure `ATTENDANCE_REQUIRE_GEOFENCE` per environment
- Enable Redis for cache and queues when available (`REDIS_ENABLED=true`)

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
