# Enterprise Upgrade — Step 3 Complete

## Service layer

Business logic moved out of controllers into final action/service classes under `app/Services/`.

### Attendance

| Class | Responsibility |
|-------|----------------|
| `GeofenceService` | Haversine distance check against office radius |
| `AttendanceCalculator` | Segment-based `total_minutes`, `overtime_minutes`, `hours_worked` |
| `AttendancePresenter` | Dashboard Inertia payload |
| `ClockInEmployeeAction` | Geofence validation, attendance + first segment (transaction) |
| `ClockOutEmployeeAction` | Close segment, mark completed, recalculate totals |
| `PauseAttendanceAction` | End open segment, set status `paused` |
| `ResumeAttendanceAction` | New segment, set status `active` |

**Routes:** `attendance.pause`, `attendance.resume` (throttled like clock-in/out).

**Config:** `config/enterprise.php` → `attendance.standard_work_minutes`, `attendance.require_geofence`.

**Env:** `ATTENDANCE_STANDARD_MINUTES=480`, `ATTENDANCE_REQUIRE_GEOFENCE=true` (set `false` for local dev without GPS).

### Tasks

| Class | Responsibility |
|-------|----------------|
| `TaskDependencyValidator` | Blocks move to `in_progress` if dependencies are not `done` |
| `MoveTaskAction` | Status/position updates with dependency guard |

### Leave

| Class | Responsibility |
|-------|----------------|
| `SubmitLeaveRequestAction` | Creates request + tier 1 & 2 `leave_approvals` rows |
| `ApproveLeaveRequestAction` | Team Lead → advances to HR tier; HR → final approve |
| `DenyLeaveRequestAction` | Denies current tier and sets request `denied` |

`LeaveRequestPolicy::review` already gates by `current_approval_tier`.

### Exceptions → Inertia flash

`AttendanceStateException`, `GeofenceViolationException`, `TaskDependencyBlockedException`, `LeaveApprovalException` render as `error` flash on Inertia requests (`bootstrap/app.php`).

### Controllers (thin)

- `AttendanceController` — delegates to attendance actions
- `TaskController@updateStatus` — `MoveTaskAction`
- `LeaveRequestController` — leave actions; index includes approval tier data
- `DashboardController` — `AttendancePresenter`, `requiresGeolocation` flag

### Frontend

`Dashboard.jsx` — pause/resume/clock-out buttons, optional geolocation + browser fingerprint on clock-in/resume.

## Next step (awaiting your approval)

**Step 4 — Broadcasting & API resources:** domain events (`TaskStatusUpdated`, `AttendanceClockedIn`, etc.), Reverb channels, Inertia-friendly API resources.
