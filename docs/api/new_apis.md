# New API Endpoints

This document describes the APIs recently added to support account sharing and related behavior. All API endpoints below require authentication unless noted otherwise. Use Bearer token (Sanctum) in `Authorization` header.

## Authentication
- Header: `Authorization: Bearer <token>`

---

## 1) List shared users

- Method: `GET`
- Path: `/api/shares`
- Auth: required

Request: none (apart from auth)

Successful response (200):

```
{
  "message": "Shared users",
  "status": true,
  "data": {
    // paginated users (page, per_page etc.)
  }
}
```

---

## 2) Share account with one or many users

- Method: `POST`
- Path: `/api/shares`
- Auth: required

You may provide either singular fields or arrays. At least one identifier is required.

Request body examples (JSON):

- Single email:

```
{
  "email": "child@example.com"
}
```

- Single phone:

```
{
  "phone": "+1234567890"
}
```

- Multiple recipients (emails and/or phones):

```
{
  "emails": ["a@example.com", "b@example.com"],
  "phones": ["+111111111", "+222222222"]
}
```

Validation errors (422): missing both email/phone or invalid formats.

Possible responses:

- Success (201):

```
{
  "message": "Users shared",
  "status": true,
  "data": {
    "shared": [ /* array of user objects that were found & attached */ ],
    "not_found": [ /* array of identifiers not matched: {email:..} or {phone:..} */ ],
    "self_skipped": [ /* any skipped because owner tried to share with themselves (ids) */ ]
  }
}
```

- No matched targets (404):

```
{
  "message": "No target users found",
  "status": false,
  "data": { "not_found": [ /* ... */ ] }
}
```

### `meta` (permissions)

You may include an optional `meta` object in the `POST /api/shares` body to apply the same permissions metadata to every share created in that request. The server stores `meta` as JSON on the `user_shares` pivot row.

Suggested permission schema (example):

```
"meta": {
  "can_view": true,
  "can_control": false,
  "can_notify": true
}
```

Example request with `meta`:

```
{
  "emails": ["child@example.com"],
  "meta": { "can_view": true, "can_control": false }
}
```

Note: `POST /api/shares` response's `shared` array returns the user objects that were attached. To inspect per-share `meta` values, use `GET /api/shares` which returns the `user_shares` rows including `meta` and the related `user`.

---

## 3) Remove a share

- Method: `DELETE`
- Path: `/api/shares/{id}`
- Auth: required
- Path param: `{id}` is the shared user's id (the child user)

Success (200):

```
{
  "message": "Share removed",
  "status": true,
  "data": null
}
```

Not found (404) if the share relation does not exist.

---

### GET /api/shares (returned structure)

The `GET /api/shares` endpoint returns paginated `user_shares` rows. Each item includes the pivot `meta` and the related `user` object. Example item:

```
{
  "id": 55,                // user_shares id
  "owner_id": 1,
  "user_id": 23,
  "meta": { "can_view": true, "can_control": false },
  "created_at": "...",
  "updated_at": "...",
  "user": { /* user object for user_id 23 */ }
}
```


## 4) Updated: Get current user (`me`) — devices merged with shared devices

- Method: `GET`
- Path: `/api/user`
- Auth: required

Behavior: the endpoint returns the authenticated user plus a `devices` array that combines:
- user's own devices (flagged with `shared: false`)
- devices owned by any users who shared with the authenticated user (flagged with `shared: true`)

Device item example (fields merged from Device model with extras):

```
{
  "id": 123,
  "user_id": 45,           // owner id
  "name": "Tracker",
  "serial": "ABCD1234...",
  "meta": { /* ... */ },
  "ip": "1.2.3.4",
  "created_at": "...",
  "updated_at": "...",
  // extras added by the API
  "shared": true,                // true if this device is shared to you
  "shared_owner_id": 45,         // owner id (null for own devices)
  "shared_owner_name": "Owner Name"
}
```

Response (200):

```
{
  "message": "User retrieved",
  "status": true,
  "data": {
    "user": { /* user object */ },
    "devices": [ /* array of device items as above */ ]
  }
}
```

---

## 5) Update authenticated user (onesignal)

- Method: `PATCH` or `PUT`
- Path: `/api/user`
- Auth: required

You can now send `onesignal` as an array/object to persist OneSignal information on the user record.

Request body example:

```
{
  "name": "New Name",
  "onesignal": {
    "player_id": "abcd1234",
    "device": "android"
  }
}
```

Successful response (200): returns the updated user (and devices under `data.user` when applicable):

```
{
  "message": "User updated",
  "status": true,
  "data": {
    "user": { /* user object with `onesignal` as array */ }
  }
}
```

Validation error (422) if `onesignal` is not an array/object.

---

## 6) Notify device status change (OneSignal)

- Method: `POST`
- Path: `/api/devices/{id}/notify`
- Auth: required
- Path param: `{id}` is the device id

Use this endpoint to notify the device owner and all users that the owner has shared with (the shared users) about a device status change. It sends a OneSignal notification to recipients who have `onesignal.player_id` saved on their user records.

Request body (JSON):

```
{
  "status": "online|offline|alarm|...",    // required
  "title": "Optional title",
  "message": "Optional message to include"
}
```

Behavior:
- Only the device owner or a user that the owner has shared with may call this endpoint for the device (permission check).
- The server gathers `player_id` from the owner and the owner's shared users and calls OneSignal API with `include_player_ids`.
- OneSignal configuration must be present in environment or `services.php`:
  - `ONESIGNAL_APP_ID` and `ONESIGNAL_REST_API_KEY` (or `services.onesignal.*` in config).

Success (200):

```
{
  "message": "Notifications sent",
  "status": true,
  "data": { /* OneSignal API response */ }
}
```

If no player IDs are found a 200 response is returned with a message indicating no players were available. If OneSignal is not configured a 500 is returned.

Example cURL (replace <TOKEN> and <DEVICE_ID>):

```
curl -X POST "http://localhost/api/devices/<DEVICE_ID>/notify" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"status":"alarm","title":"Device Alarm","message":"Your device triggered an alarm"}'
```


Notes & recommendations
- The `user_shares` table uses a composite unique key on (`owner_id`, `user_id`) to prevent duplicate share rows for the same owner/child pair.
- If you want per-device sharing (granting a child access to specific devices rather than all owner's devices), we can add a `device_shares` pivot that links `device_id` to `user_id`.
- Consider adding invitations for non-registered targets (store pending invites) if you want owners to share by email/phone before the target registers.

---

## PATCH /api/shares/{id} — Update a share's meta or target user

- Method: `PATCH`
- Path: `/api/shares/{id}`
- Auth: required (owner only)
- Path param: `{id}` is the `user_shares` row id

Request body examples:

Update permissions/meta only:

```
{
  "meta": { "can_view": true, "can_control": false, "can_notify": true }
}
```

Change the target user (owner must ensure uniqueness):

```
{
  "user_id": 78
}
```

Response (200): returns updated `user_shares` row (including `meta` and `user` relation):

```
{
  "message": "Share updated",
  "status": true,
  "data": {
    "id": 55,
    "owner_id": 1,
    "user_id": 78,
    "meta": { "can_view": true, "can_control": false },
    "created_at": "...",
    "updated_at": "...",
  }
}
```

Validation (422) if invalid `meta` or `user_id`, 403 if caller is not the owner, 404 if share not found.

