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

Notes & recommendations
- The `user_shares` table uses a composite unique key on (`owner_id`, `user_id`) to prevent duplicate share rows for the same owner/child pair.
- If you want per-device sharing (granting a child access to specific devices rather than all owner's devices), we can add a `device_shares` pivot that links `device_id` to `user_id`.
- Consider adding invitations for non-registered targets (store pending invites) if you want owners to share by email/phone before the target registers.
