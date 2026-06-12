# Enterprise Upgrade — Step 2 Complete

## Schema additions

### HR & attendance
| Table / columns | Purpose |
|-----------------|---------|
| `office_locations` | Geofence center + radius |
| `users.team_lead_id`, `office_location_id` | Org structure |
| `attendances.*` | status, overtime, IP, fingerprint, geofence coords |
| `attendance_segments` | Pause/resume segments per shift |

### Tasks
| Table / columns | Purpose |
|-----------------|---------|
| `tags`, `tag_task` | Task labeling |
| `tasks.estimated_minutes`, `actual_minutes` | Estimates vs logged time |
| `task_dependencies` | Finish-to-start blocking |
| `time_logs` | Per-user time entries |

### Leave (multi-tier)
| Table / columns | Purpose |
|-----------------|---------|
| `leave_requests.current_approval_tier`, `hr_finalized_at` | Workflow state |
| `leave_approvals` | Tier 1 (Team Lead) → Tier 2 (HR Admin) |

## Roles (Spatie)

| Role | Demo login |
|------|------------|
| Super Admin | `superadmin@taskkitty.test` |
| HR Admin | `hr@taskkitty.test` |
| Project Manager | `pm@taskkitty.test` |
| Team Lead | `lead@taskkitty.test` |
| Employee | `employee@taskkitty.test` |

Password: **`password`**

Legacy roles (`Admin`, `Manager`) are migrated via `MigrateLegacyRolesSeeder`.

## Models

New: `OfficeLocation`, `Tag`, `TaskDependency`, `TimeLog`, `AttendanceSegment`, `LeaveApproval`

Updated with enums, relationships, and `LogsActivityTrait` on `Task`, `Attendance`, `LeaveRequest`.

## Factories

`OfficeLocationFactory`, `ProjectFactory`, `TaskFactory`, `TagFactory`, `TimeLogFactory`, `LeaveRequestFactory`, `AttendanceFactory`

## Commands

```bash
php artisan migrate
php artisan db:seed
# or fresh:
php artisan migrate:fresh --seed
```

## Next step

**Step 3 — Service layer** (`ClockInEmployeeAction`, `MoveTaskAction`, leave approval workflow, geofence validation).

Reply **yes** to proceed.
