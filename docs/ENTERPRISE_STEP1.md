# Enterprise Upgrade — Step 1 Complete

## Codebase baseline (before Step 1)

| Area | Status |
|------|--------|
| Laravel | 12.x |
| React + Inertia + Tailwind | Installed (Breeze) |
| spatie/laravel-permission | Installed (roles: Admin, Manager, Employee) |
| Task / Project / Attendance modules | Working MVP |

## Step 1 — Installed & configured

### PHP packages (`composer.json`)

| Package | Version | Purpose |
|---------|---------|---------|
| `spatie/laravel-activitylog` | 4.12.3 | Audit logging (PHP 8.2 / L12 compatible) |
| `spatie/laravel-medialibrary` | ^11.23 | File attachments |
| `laravel/reverb` | ^1.10 | WebSocket server |
| `spatie/laravel-permission` | ^6.25 | RBAC (existing) |

**Not installed (platform limits):**

- `laravel/horizon` — requires `ext-pcntl` / `ext-posix` (Linux/macOS). Use `php artisan queue:listen` on Windows, or deploy Horizon on production Linux.
- `spatie/laravel-activitylog` v5 — requires PHP 8.4+.

### Frontend (`package.json`)

| Package | Purpose |
|---------|---------|
| `zustand` | Client UI state (`resources/js/stores/useAppStore.js`) |
| `laravel-echo` + `pusher-js` | Reverb / WebSocket client |
| `@radix-ui/react-slot`, `react-checkbox`, `react-separator` | Accessible primitives |
| `class-variance-authority`, `clsx`, `tailwind-merge` | shadcn-style UI utilities |

### New structure

```
app/Models/Concerns/LogsActivityTrait.php   # Reusable audit trait
config/enterprise.php                      # Role names & infra flags
config/activitylog.php
config/reverb.php
config/broadcasting.php
resources/js/lib/utils.js                  # cn() helper
resources/js/Components/ui/Button.jsx
resources/js/Layouts/AppShell.jsx          # Enterprise shell (sidebar)
resources/js/stores/useAppStore.js
resources/js/echo.js
```

### Layout

`AuthenticatedLayout` now wraps pages in **`AppShell`** (sidebar navigation, sticky header). Existing pages continue to work.

### Migrations (run `php artisan migrate`)

- `activity_log` tables (Spatie)
- `media` table (Medialibrary)

### Environment

Copy keys from `.env.example`:

- `BROADCAST_CONNECTION=reverb` when using real-time
- `REVERB_*` and `VITE_REVERB_*` for Echo
- `REDIS_ENABLED=true` + `QUEUE_CONNECTION=redis` + `CACHE_STORE=redis` when Redis is available

### Local development

```bash
# Full stack (includes Reverb)
composer dev

# Without Reverb (Windows-friendly)
composer dev:no-reverb
```

## Next step (awaiting your approval)

**Step 2 — Database schema & factories** for advanced models (tags, dependencies, time logs, geofencing, multi-tier leave, new roles, etc.).

Reply **yes** to proceed to Step 2.
