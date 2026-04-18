# New API Endpoints — sharing, per-device permissions, and notifications

This document describes the APIs that manage account sharing, per-share permissions (`meta`), and related behavior. All API endpoints below require authentication unless noted otherwise. Use Bearer token (Sanctum) in the `Authorization` header.

Authentication
- Header: `Authorization: Bearer <token>`

Overview
- Sharing is modelled with two pivot tables:
  - `user_shares (owner_id, user_id, meta)` — owner-level shares that apply to all owner's devices unless overridden.
  - `device_shares (device_id, user_id, meta)` — per-device shares that override owner-level `meta` for that device.
- The APIs prefer device-level `meta` when available and fall back to owner-level `user_shares.meta`.

1) List shared users (grouped, per-device)
- Method: `GET`
- Path: `/api/shares`
- Auth: required

Description
- Returns a compact grouped list: for each user the authenticated owner has shared with, the response lists only the owner's devices that the shared user has access to along with effective permissions for each device.

Effective permissions resolution
1. If a `device_shares` row exists for (device_id, user_id), use its `meta` (device-level permissions).
2. Otherwise, if an owner-level `user_shares.meta` exists for that user, use it as a fallback (applies to all devices unless overridden).
3. If neither exists, the device is excluded for that user.

Response example (200):

```
{
  "message": "Shared users",
  "status": true,
  "data": [
    {
      "user_id": 23,
      "user": { /* user object */ },
      "devices": [
        { "device_id": 2, "device_share_id": 14, "name": "Garage Door", "serial": "GEC...", "permissions": { "can_open": true, "can_close": true } },
        { "device_id": 5, "device_share_id": null, "name": "Back Door", "serial": "ESP...", "permissions": { "can_open": true, "can_close": false } }
      ]
    },
    {
      "user_id": 42,
      "user": { /* user object */ },
      "devices": [
        { "device_id": 3, "device_share_id": 18, "name": "Front Gate", "serial": "XYZ...", "permissions": { "can_view": true } }
      ]
    }
  ]
}
```

Notes
- The response is intentionally compact to minimize client requests — the mobile app can render a homepage with a single fetch.

2) Share account with one or many users (create shares)
- Method: `POST`
- Path: `/api/shares`
- Auth: required (owner only)

Request body
- Provide at least one identifier: `email`, `phone`, or arrays `emails`, `phones`.
- Optional: `meta` — an object stored on the `user_shares` row and used as the default permissions for that shared user.

Examples
- Single email

```
{ "email": "child@example.com" }
```

- Multiple recipients with shared `meta`

```
{
  "emails": ["a@example.com","b@example.com"],
  "meta": { "permissions": { "can_view": true, "can_control": false } }
}
```

Responses
- Success (201): returns `shared` (found & attached users), `not_found`, and `self_skipped` arrays.
- If no target users are matched the endpoint returns 404 with `not_found`.

3) Remove a share (owner removes a child)
- Method: `DELETE`
- Path: `/api/shares/{id}` — `{id}` is the child user's id (the `user_id` in the pivot)
- Auth: required (owner only)

Response (200):

```
{ "message": "Share removed", "status": true, "data": null }
```

4) Update an owner-level share (`user_shares`)
- Method: `PATCH`
- Path: `/api/shares/{id}` — `{id}` is the `user_shares` row id
- Auth: required (owner only)

Request body examples
- Update only `meta`:

```
{ "meta": { "permissions": { "can_view": true, "can_control": false } } }
```

- Change the target `user_id` (owner must ensure uniqueness):

```
{ "user_id": 78 }
```

Response (200): returns the updated `user_shares` row including `meta` and related `user`.

5) Per-device shares — grant/revoke per-device access (`device_shares`)
- Purpose: Use when you want to grant a child access to specific devices instead of all owner's devices, or to assign distinct permissions per device.

- Create/update per-device share
  - Method: `POST`
  - Path: `/api/device-shares`
  - Auth: required (owner only)
  - Body: `{ "device_id": <id>, "user_id": <child_user_id>, "meta": { ... } }`

Response (201): returns the created `device_shares` row.

- Update an existing device share
  - Method: `PATCH`
  - Path: `/api/device-shares/{id}` — `{id}` is the `device_shares` row id
  - Body: `{ "meta": { ... } }`

- Delete a device share
  - Method: `DELETE`
  - Path: `/api/device-shares/{id}`

Notes
- The server prefers `device_shares.meta` when building effective permissions for a device; otherwise it falls back to `user_shares.meta`.

6) Get current user (`me`) — merged devices with `share_meta`
- Method: `GET`
- Path: `/api/user`
- Auth: required

Description
- Returns the authenticated user and a `devices` array that contains:
  - the user's own devices (each with `shared: false`), and
  - devices owned by other users who have shared with the authenticated user (each with `shared: true` and owner info).
- Each shared device includes a `share_meta` field with the effective permissions for the authenticated user on that device (device-level meta preferred; otherwise owner-level meta).

Device example

```
{
  "id": 123,
  "user_id": 45,           // owner id
  "name": "Tracker",
  "serial": "ABCD1234...",
  "meta": { /* device metadata */ },
  "ip": "1.2.3.4",
  "created_at": "...",
  "updated_at": "...",
  "shared": true,                // true if this device is shared to you
  "shared_owner_id": 45,
  "shared_owner_name": "Owner Name",
  "share_meta": { "permissions": { "can_view": true } }
}
```

Response (200):

```
{ "message": "User retrieved", "status": true, "data": { "user": { /* user */ }, "devices": [ /* devices */ ] } }
```

7) Update authenticated user (persist OneSignal info)
- Method: `PATCH` or `PUT`
- Path: `/api/user`
- Auth: required

Body example to store OneSignal player id:

```
{ "onesignal": { "player_id": "abcd1234", "device": "android" } }
```

Response (200): returns the updated user including the `onesignal` JSON stored on the user record.

8) Notify device status change (OneSignal)
- Method: `POST`
- Path: `/api/devices/{id}/notify`
- Auth: required

Description
- Notifies the device owner and all users the owner has shared with (recipients are deduplicated). Only the owner or a user the owner has shared with may call this endpoint for the device.
- The server collects `onesignal.player_id` values from recipients and calls OneSignal with `include_player_ids`.

Request body

```
{ "status": "online|offline|alarm|...", "title": "Optional title", "message": "Optional message" }
```

Success (200): returns OneSignal API response or a message indicating there were no players to notify. If OneSignal is not configured a 500 is returned.

OneSignal configuration
- Add to `.env`:
  - `ONESIGNAL_APP_ID=...`
  - `ONESIGNAL_REST_API_KEY=...`
- Or set `services.onesignal` keys and run `php artisan config:clear`.

Example cURL

```
curl -X POST "http://localhost/api/devices/<DEVICE_ID>/notify" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"status":"alarm","title":"Device Alarm","message":"Your device triggered an alarm"}'
```

Other API endpoints that involve `meta` or shared data
- `POST|PATCH|DELETE /api/device-shares` — create/update/delete per-device shares (see section 5).
- `POST /api/shares` supports a request-level `meta` that will be stored on each created `user_shares` row.
- Admin device endpoints (`/admin/devices` create/update) accept `meta` for device records; admin UI and controllers persist device `meta`.

Recommendations & next steps
- If you currently have owner-level `user_shares.meta` and want per-device defaults, consider running a one-shot migration that copies owner-level `meta` into `device_shares` for each owner device (I can prepare this migration script).
- Consider validating `meta.permissions` with an explicit schema if you want stricter enforcement (e.g., allowed keys and boolean values).

Errors and validation
- 422: validation errors (missing fields or invalid `meta` shapes).
- 403: forbidden (attempt to manage shares or device-shares as a non-owner).
- 404: not found (share or device not found).

Changes summary
- The main documentation changes are: returning grouped per-user device lists in `GET /api/shares`, adding `share_meta` to devices returned by `GET /api/user`, and documenting `device_shares` for per-device permissions.
