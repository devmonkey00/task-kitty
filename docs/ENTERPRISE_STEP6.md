# Enterprise Upgrade — Step 6 Complete

## Optimistic Kanban

Dragging a task updates the board **immediately** before the server responds.

- On failure, the board **rolls back** to the previous state and shows an error.
- Pending cards show a subtle indigo ring while the request is in flight.
- Echo updates from **other users** are ignored for tasks you are currently moving (avoids flicker).

**Files:** `KanbanBoard.jsx`, `Projects/Show.jsx`, `lib/kanban.js` (`cloneBoard`, `moveTaskOptimistically`).

## Connection status indicator

Header badge (all authenticated pages via `AppShell`):

| State | Meaning |
|-------|---------|
| Realtime off | `BROADCAST_CONNECTION` not reverb/pusher |
| Connecting… | WebSocket handshake in progress |
| Live | Connected |
| Disconnected / Failed | Lost connection — **Reconnect** button |

**Files:** `RealtimeStatusIndicator.jsx`, `useEchoConnection.js`, `useAppStore` (`connectionStatus`).

## Reconnection UX

- Pusher-js auto-reconnects by default.
- Manual **Reconnect** calls `pusher.connect()` and sets status to connecting.
- Project Kanban shows an amber banner when broadcasting is enabled but socket is disconnected.
- Echo client uses extended `activityTimeout` / `pongTimeout` for stabler long sessions.

## Enterprise upgrade complete

Steps 1–6 are implemented. See `docs/ENTERPRISE_STEP1.md` through `ENTERPRISE_STEP6.md` for the full trail.

**Production checklist:**
- `BROADCAST_CONNECTION=reverb` + Reverb server running
- `php artisan queue:listen` if switching events to queued broadcast later
- `ATTENDANCE_REQUIRE_GEOFENCE` per environment
- Run `npm run build` after frontend changes
