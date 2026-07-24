# EduFlow Flutter API Contract

Base URL: `/api/v1`

Authentication: Laravel Sanctum bearer token.

This contract contains only the REST endpoints required by the Flutter student application. Administrator account approval and rejection are **not API endpoints**. They will be implemented later as actions in the Filament dashboard.

## Request conventions

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <token>
```

- JSON fields use camelCase.
- Compatibility aliases accepted by the API include `full_name`, `telegram_username`, and `device_name`.
- Validation errors return HTTP `422`.
- Missing or inaccessible records return HTTP `404`.
- Pending, rejected, or expired access returns HTTP `403`.
- Collections use Laravel pagination with `data`, `links`, and `meta`.

## Complete Flutter endpoint list

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

## Account lifecycle

```text
registration
  -> OTP verification
  -> pending Filament administrator review
  -> approved OR rejected
```

Account statuses:

```text
pending
approved
rejected
```

After OTP verification, the API returns a Sanctum token while the account remains pending. Flutter uses this token to request `/me/account-status` but cannot access approved-only routes.

Approval and rejection will be managed from the Filament dashboard by updating the account status and optional rejection reason. No `/admin/*` account-approval API endpoints exist.

## User response

Authentication and profile responses use the following user properties:

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

Telegram usernames are normalized to lowercase and stored without the leading `@`.

## Authentication

### POST `/auth/register`

Creates an unverified account with `accountStatus: pending` and generates a six-digit OTP.

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

Telegram username validation:

```text
required
unique
3-64 characters
letters, numbers, and underscores only
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

Request:

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
debugOtp in local/testing only
```

### POST `/auth/login`

Request:

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

Possible `nextAction` values:

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

Revokes the current access token.

### POST `/auth/logout-all`

Revokes every token belonging to the user.

## Profile and approval status

### GET `/me`

Returns the authenticated user resource.

### GET `/me/account-status`

Returns the authenticated user resource for splash, pending, and rejection screens.

Flutter routing:

```text
accountStatus = approved -> main screen
accountStatus = pending  -> pending approval screen
accountStatus = rejected -> rejection screen using rejectionReason
HTTP 401                 -> clear token and open login
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

Rules:

- Phone is not editable through this endpoint.
- Changing Telegram username resets a non-admin account to `pending`.
- Previous review and rejection data are cleared after a Telegram username change.
- The account must be reviewed again later through the Filament dashboard.

### POST `/me/avatar`

Multipart form-data:

```text
avatar: <image>
```

## Approved-account middleware response

Pending account accessing an approved-only route:

```json
{
  "message": "Your account is pending administrator approval.",
  "accountStatus": "pending",
  "rejectionReason": null
}
```

Rejected account:

```json
{
  "message": "Your account was rejected by the administrator.",
  "accountStatus": "rejected",
  "rejectionReason": "Telegram account could not be verified."
}
```

Flutter must redirect using `accountStatus` when it receives this HTTP `403` response.

## Future Filament approval workflow

Account review will be implemented in the Filament dashboard, not through REST endpoints.

The future Filament interface should provide:

- A users table filtered by `accountStatus = pending`.
- Search by name, phone, and Telegram username.
- View user registration and Telegram details.
- Approve action that sets `accountStatus = approved`.
- Reject action that sets `accountStatus = rejected` and requires `rejectionReason`.
- Store `accountReviewedAt` and the reviewing dashboard administrator internally.
- Prevent reviewing admin accounts or accounts that are no longer pending.

Flutter only observes the result through `GET /me/account-status`.

## Public catalog

Public catalog endpoints return metadata only. Paid lesson media and assessment answers are never exposed publicly.

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

Requires an approved account.

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

Requires an approved account. Rate limit: ten requests per minute.

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

Redemption is transactional and idempotent for the same user and code.

## My courses

### GET `/me/enrollments`

Returns paginated enrollments ordered by recent access.

Expired enrollment properties:

```text
status = expired
isExpired = true
```

### GET `/me/enrollments/{enrollment}`

Returns an enrollment only when it belongs to the authenticated user.

### GET `/me/courses/{course}`

Returns the personalized course payload required by course details, video playback, downloads, and progress screens.

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

Requires an approved account and an active course enrollment.

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
- Reaching lesson duration marks the lesson completed.
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
pending      -> pending approval screen
rejected     -> rejection screen
401          -> clear token and open login
```

On approved-only HTTP `403`:

```text
pending  -> pending approval screen
rejected -> rejection screen with rejectionReason
```

## Demo data

After:

```bash
php artisan migrate:fresh --seed
```

```text
Approved student phone: +963999999999
Approved student Telegram: eduflow_student
Approved student password: password123

Demo QR code: EDUFLOW-DEMO-2026
```

## Deliberately excluded REST endpoints

The Flutter API does not expose:

- Account approval or rejection endpoints
- Filament/dashboard administration endpoints
- Course, category, module, or lesson management CRUD
- Course and lesson media-management routes
- Assessment authoring, question management, attempts, results, or grading
- Notification-template CRUD
- Reports and exports
- Direct unaudited enrollment routes

All administrator functionality belongs in the Filament dashboard.
