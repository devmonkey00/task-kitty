# Enterprise Upgrade — Step 4 Complete

## Broadcasting (Reverb)

Events use `ShouldBroadcastNow` so updates are immediate without a queue worker.

| Event | Channel | Event name |
|-------|---------|------------|
| `TaskStatusUpdated` | `private-projects.{id}` | `task.status.updated` |
| `TaskCreated` | `private-projects.{id}` | `task.created` |
| `AttendanceUpdated` | `private-users.{id}` | `attendance.updated` |
| `LeaveRequestUpdated` | `private-leave-requests` | `leave.updated` |

Channel authorization: `routes/channels.php` (project view policy, user self, leave approvers).

Broadcast auth: `bootstrap/app.php` → `withBroadcasting(..., middleware: ['web', 'auth'])`.

## API resources

| Resource | Used for |
|----------|----------|
| `TaskResource` | Kanban, task show, broadcast payloads |
| `ProjectResource` | Project index |
| `AttendanceResource` | Broadcast record payload |
| `LeaveRequestResource` | Leave index + broadcast |
| `LeaveApprovalResource` | Nested on leave requests |

`FormatsTaskData` now resolves via `TaskResource`.

## Frontend Echo hooks

| Hook | Page |
|------|------|
| `useProjectChannel` | `Projects/Show` — live Kanban updates |
| `useUserAttendanceChannel` | `Dashboard` — live attendance state |
| `useLeaveRequestsChannel` | `Leave/Index` — approver queue updates |

Shared helper: `resources/js/lib/kanban.js` (`applyTaskToBoard`, `isBroadcastingEnabled`).

Inertia shared prop: `enterprise.broadcastingEnabled` (true when `BROADCAST_CONNECTION` is `reverb` or `pusher`).

## Enable real-time locally

```env
BROADCAST_CONNECTION=reverb
# REVERB_* and VITE_REVERB_* from .env.example
```

```bash
php artisan reverb:start
composer dev   # or npm run dev + php artisan serve
```

With `BROADCAST_CONNECTION=log`, the app works normally; Echo hooks stay inactive.

## Next step (awaiting your approval)

**Step 5 — HR dashboard UI:** secure clock-in UX, multi-tier leave review UI, attendance tables for HR.
