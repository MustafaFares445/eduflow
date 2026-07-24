# EduFlow Flutter API Contract

Base URL: `/api/v1`

Authentication: Laravel Sanctum bearer token.

This is the complete contract for the current Flutter student application and the admin account-approval flow.

## Request conventions

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <token>
```

- JSON fields use camelCase.
- `full_name`, `telegram_username`, `device_name`, and `rejection_reason` are accepted as compatibility aliases.
- Validation errors return HTTP `422`.
- Missing/inaccessible records return HTTP `404`.
- Pending, rejected, or expired access returns HTTP `403`.
- Collections use Laravel pagination: `data`, `links`, and `meta`.

## Complete endpoint list

### Public authentication

```text
POST /auth/register
POST /auth/verify-otp
POST /auth/resend-otp
POST /auth/login
```

### Public catalog

```text
GET /catalog/categories
GET /catalog/courses
GET /catalog/courses/{course}
```

### Authenticated account routes

Available to pending, approved, and rejected users:

```text
POST  /auth/logout
POST  /auth/logout-all
GET   /me
GET   /me/account-status
PATCH /me
POST  /me/avatar
```

### Approved student routes

```text
GET   /me/learning-summary
GET   /me/enrollments
GET   /me/enrollments/{enrollment}
GET   /me/courses/{course}
POST  /enrollment-codes/redeem
PATCH /lessons/{lesson}/progress
GET   /notifications
PATCH /notifications/{notification}/read
PATCH /notifications/read-all
GET   /me/notification-preferences
PATCH /me/notification-preferences
```

### Admin account-review routes

```text
GET   /admin/users/pending
PATCH /admin/users/{user}/approve
PATCH /admin/users/{user}/reject
```

## Account lifecycle

```text
registration
  -> OTP verification
  -> pending admin review
  -> approved OR rejected
```

Account statuses:

```text
pending
approved
rejected
```

After OTP verification, the API returns a token while the user remains pending. Flutter uses the token to check `/me/account-status` but cannot access approved-only routes.

## User response

```json
{
  "id": 10,
  "name": "EduFlow Student",
  "email": null,
  "phone": "+963999999999",
  "telegramUsername": "eduflow_student",
  "phoneVerifiedAt": "2026-07-24T12:00:00.000000Z",
  "accountStatus": "pending",
  "isPending": true,
  "isApproved": false,
  "isRejected": false,
  "rejectionReason": null,
  "accountReviewedAt": null,
  "accountReviewedBy": null,
  "isAdmin": false,
  "bio": null,
  "timezone": "Asia/Damascus",
  "locale": "ar",
  "isActive": true,
  "lastLoginAt": "2026-07-24T12:00:00.000000Z",
  "avatarUrl": null,
  "createdAt": "2026-07-24T11:55:00.000000Z",
  "updatedAt": "2026-07-24T12:00:00.000000Z"
}
```

Telegram usernames are normalized to lowercase and stored without `@`.

## Authentication

### POST `/auth/register`

Request:

```json
{
  "fullName": "EduFlow Student",
  "phone": "+963999999999",
  "telegramUsername": "@EduFlow_Student",
  "password": "password123",
  "locale": "ar",
  "timezone": "Asia/Damascus"
}
```

Accepted aliases:

```text
full_name -> fullName
telegram_username -> telegramUsername
```

Telegram username rules:

```text
required
unique
3-64 characters
letters, numbers, underscores
normalized to lowercase
leading @ removed
```

Response: HTTP `201`

```json
{
  "user": {
    "phone": "+963999999999",
    "telegramUsername": "eduflow_student",
    "accountStatus": "pending",
    "phoneVerifiedAt": null
  },
  "message": "Verification code sent successfully.",
  "nextAction": "verify_phone",
  "verificationExpiresAt": "2026-07-24T12:10:00.000000Z",
  "debugOtp": "123456"
}
```

`debugOtp` is returned only in `local` and `testing`. Production must connect an SMS provider.

### POST `/auth/verify-otp`

```json
{
  "phone": "+963999999999",
  "otp": "123456",
  "deviceName": "Android phone"
}
```

Response:

```json
{
  "user": {
    "accountStatus": "pending",
    "phoneVerifiedAt": "2026-07-24T12:00:00.000000Z"
  },
  "token": "1|sanctum-token",
  "message": "Phone verified. Your account is pending administrator approval.",
  "nextAction": "await_admin_approval"
}
```

Flutter saves the token and opens the pending approval screen.

### POST `/auth/resend-otp`

Rate limit: three requests per minute.

```json
{
  "phone": "+963999999999"
}
```

Response fields:

```text
message
nextAction = verify_phone
verificationExpiresAt
debugOtp in local/testing
```

### POST `/auth/login`

```json
{
  "phone": "+963999999999",
  "password": "password123",
  "deviceName": "Android phone"
}
```

Response fields:

```text
user
token
message
nextAction
```

`nextAction` values:

```text
open_app              approved account
await_admin_approval  pending account
show_rejection        rejected account
```

Rejected response example:

```json
{
  "user": {
    "accountStatus": "rejected",
    "rejectionReason": "Telegram account could not be verified."
  },
  "token": "1|sanctum-token",
  "message": "Your account was rejected by the administrator.",
  "nextAction": "show_rejection"
}
```

Unverified or inactive accounts cannot log in.

### POST `/auth/logout`

Revokes the current token.

### POST `/auth/logout-all`

Revokes all tokens belonging to the user.

## Profile and approval status

### GET `/me`

Returns the authenticated user resource.

### GET `/me/account-status`

Returns the authenticated user resource and is used by splash/pending/rejection screens.

Flutter routing:

```text
accountStatus = approved -> main screen
accountStatus = pending  -> pending screen
accountStatus = rejected -> rejection screen using rejectionReason
HTTP 401                 -> clear token and login
```

### PATCH `/me`

Editable fields:

```json
{
  "fullName": "Updated Student",
  "telegramUsername": "@updated_student",
  "bio": "Learning with EduFlow",
  "locale": "ar",
  "timezone": "Asia/Damascus"
}
```

- Phone is not editable through this endpoint.
- Changing Telegram username resets a non-admin account to `pending` and clears the previous review/rejection data.
- The updated account must be reviewed again by an admin.

### POST `/me/avatar`

Multipart form-data:

```text
avatar: <image>
```

## Approval middleware response

Pending user accessing an approved-only route:

```json
{
  "message": "Your account is pending administrator approval.",
  "accountStatus": "pending",
  "rejectionReason": null
}
```

Rejected user:

```json
{
  "message": "Your account was rejected by the administrator.",
  "accountStatus": "rejected",
  "rejectionReason": "Telegram account could not be verified."
}
```

Flutter must redirect based on `accountStatus` when receiving this HTTP `403` response.

## Admin approval API

These routes require an authenticated user with `isAdmin: true`.

### GET `/admin/users/pending`

Query parameters:

```text
perPage
search
```

`search` checks:

```text
name
phone
telegramUsername
```

Returns a paginated list of pending non-admin users.

### PATCH `/admin/users/{user}/approve`

No body is required.

```json
{
  "data": {
    "id": 10,
    "accountStatus": "approved",
    "isApproved": true,
    "rejectionReason": null,
    "accountReviewedAt": "2026-07-24T12:30:00.000000Z",
    "accountReviewedBy": 1
  },
  "message": "User account approved successfully."
}
```

### PATCH `/admin/users/{user}/reject`

```json
{
  "rejectionReason": "Telegram account could not be verified."
}
```

Alias:

```text
rejection_reason -> rejectionReason
```

Response:

```json
{
  "data": {
    "id": 10,
    "accountStatus": "rejected",
    "isRejected": true,
    "rejectionReason": "Telegram account could not be verified."
  },
  "message": "User account rejected successfully."
}
```

Only pending non-admin accounts can be approved or rejected.

## Public catalog

Public catalog endpoints return metadata only. Paid media and assessment answers are never exposed publicly.

### GET `/catalog/categories`

Returns the active category tree.

Category fields:

```text
id
parentId
name
slug
description
icon
color
imageUrl
isActive
sortOrder
coursesCount
children
```

### GET `/catalog/courses`

Query parameters:

```text
perPage
search
filter[categoryId]
filter[level]
sort
```

Course list data includes `coverUrl` and `introVideoUrl` when available.

### GET `/catalog/courses/{course}`

Returns public course metadata only. Modules, paid lessons, media URLs, and assessment answers are excluded.

## Learning summary

### GET `/me/learning-summary`

Requires approved account.

```json
{
  "data": {
    "coursesCount": 3,
    "activeCoursesCount": 2,
    "completedCoursesCount": 1,
    "completedLessonsCount": 18,
    "learningMinutes": 720
  }
}
```

## QR course activation

### POST `/enrollment-codes/redeem`

Requires approved account. Rate limit: ten requests per minute.

```json
{
  "code": "EDUFLOW-DEMO-2026"
}
```

Response:

```json
{
  "data": {
    "id": 1,
    "courseId": 1,
    "status": "active",
    "progressPercentage": "0.00",
    "enrolledAt": "2026-07-24T12:00:00.000000Z",
    "expiresAt": "2027-07-24T12:00:00.000000Z",
    "isExpired": false,
    "course": {}
  },
  "message": "Course activated successfully."
}
```

Redemption is transactional and idempotent for the same user/code.

## My courses

### GET `/me/enrollments`

Returns paginated enrollments ordered by recent access.

Expired enrollments return:

```text
status = expired
isExpired = true
```

### GET `/me/enrollments/{enrollment}`

Returns an enrollment only when it belongs to the authenticated user.

### GET `/me/courses/{course}`

Returns the personalized course payload needed by course details, video playback, files, and progress screens.

```json
{
  "data": {
    "course": {},
    "enrollment": {},
    "summary": {
      "videoCount": 8,
      "fileCount": 4,
      "completedLessonsCount": 3,
      "resumeLessonId": 7
    },
    "modules": [
      {
        "id": 1,
        "title": "Module",
        "position": 1,
        "lessons": [
          {
            "id": 7,
            "title": "Lesson",
            "type": "video",
            "durationSeconds": 420,
            "isPreview": false,
            "canAccess": true,
            "progress": {
              "status": "in_progress",
              "progressSeconds": 180,
              "progressPercentage": 42.86,
              "completedAt": null,
              "lastAccessedAt": "2026-07-24T12:00:00.000000Z"
            },
            "video": {
              "id": 20,
              "fileName": "lesson.mp4",
              "mimeType": "video/mp4",
              "url": "https://example.test/lesson.mp4",
              "thumbnailUrl": null,
              "size": 123456
            },
            "files": [],
            "assessment": null
          }
        ]
      }
    ]
  }
}
```

Returns:

```text
404 when the user is not enrolled
403 when the enrollment is expired
```

## Lesson progress

### PATCH `/lessons/{lesson}/progress`

Requires an approved account and active course enrollment.

```json
{
  "status": "in_progress",
  "progressSeconds": 180
}
```

Allowed statuses:

```text
not_started
in_progress
completed
```

Rules:

- Progress is clamped between zero and lesson duration.
- Reaching lesson duration marks it completed.
- Enrollment progress and last-accessed time are recalculated.
- There is no separate complete endpoint; use `status: completed`.

## Notifications

All notification endpoints require an approved account.

### GET `/notifications`

Returns paginated notifications owned by the authenticated user.

### PATCH `/notifications/{notification}/read`

Marks one owned notification as read and returns it inside `data`.

### PATCH `/notifications/read-all`

Marks all authenticated-user notifications as read.

### GET `/me/notification-preferences`

Returns notification preferences.

### PATCH `/me/notification-preferences`

Updates notification preferences.

## Flutter navigation requirements

After registration:

```text
nextAction = verify_phone -> OTP screen
```

After OTP verification or login:

```text
nextAction = open_app              -> main screen
nextAction = await_admin_approval  -> pending approval screen
nextAction = show_rejection        -> rejection screen
```

During splash:

```text
stored token -> GET /me/account-status
approved     -> main screen
pending      -> pending screen
rejected     -> rejection screen
401          -> clear token and login
```

On approved-only HTTP `403`:

```text
pending  -> pending screen
rejected -> rejection screen with rejectionReason
```

## Demo accounts

After:

```bash
php artisan migrate:fresh --seed
```

```text
Approved student phone: +963999999999
Approved student Telegram: eduflow_student
Approved student password: password123

Admin phone: +963988888888
Admin Telegram: eduflow_admin
Admin password: password123

Demo QR code: EDUFLOW-DEMO-2026
```

## Deliberately removed mobile routes

The Flutter API does not expose:

- Course/category/module/lesson management CRUD
- Course and lesson media-management routes
- Assessment authoring, question management, attempts, results, or grading
- Notification-template CRUD
- Reports and exports
- Direct unaudited enrollment routes

These belong in a separate administrative content-management surface.
