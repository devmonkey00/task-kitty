# Enterprise Upgrade — Step 5 Complete

## HR dashboard (`/hr`)

**Access:** Super Admin, HR Admin (`canViewHrDashboard()`).

**Features:**
- Daily attendance stats (active, paused, completed, overtime)
- Filterable paginated table (date, status, employee search)
- Columns: employee, status, clock times, hours, overtime, office, geofence, masked IP
- Link to pending leave queue

**Backend:** `HrDashboardController`, `ListHrAttendancesAction`, `HrAttendanceResource`, `AttendancePolicy`.

## Secure clock-in UX

`AttendanceClockPanel` component (used on employee Dashboard):
- Geofence + fingerprint messaging
- Location error feedback when GPS denied
- Pause / resume / clock-out actions

## Multi-tier leave UI

**Leave index** (`/leave-requests`):
- Tabs: **Needs my action** | **All requests** (approvers) or **My requests** (employees)
- Approval workflow steps (Team Lead → HR Admin)
- Tier-specific buttons: “Approve as Team Lead” / “Finalize as HR”
- Optional denial comments
- HR link to attendance dashboard

**Backend:** `LeaveReviewPresenter`, `DenyLeaveRequestRequest` (comments).

## Navigation

Sidebar **HR** link visible when `canViewHrDashboard` (shared Inertia prop).

## Demo logins

| Role | Email | Password |
|------|-------|----------|
| HR Admin | hr@taskkitty.test | password |
| Team Lead | lead@taskkitty.test | password |
| Employee | employee@taskkitty.test | password |

## Next step

**Step 6** — complete. See `docs/ENTERPRISE_STEP6.md`.
