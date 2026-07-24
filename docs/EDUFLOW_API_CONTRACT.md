# EduFlow Flutter API Contract

Base URL: `/api/v1`

Authentication: Laravel Sanctum bearer tokens.

This document is the complete API contract required by the current Flutter student application, including phone authentication, OTP verification, Telegram username, administrator account approval, catalog browsing, QR course activation, course progress, profile statistics, and notifications.

## Conventions

- Request and response properties use camelCase.
- Backward-compatible Flutter aliases such as `full_name` and `telegram_username` are accepted where documented.
- Protected routes require:

```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

- Collection responses use Laravel pagination with `data`, `links`, and `meta`.
- Validation failures use HTTP `422`.
- Missing or inaccessible records use HTTP `404`.
- Pending, rejected, or expired access uses HTTP `403`.

## Complete Endpoint Inventory

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

### Authenticated account endpoints

These routes are available while the account is pending, approved, or rejected:

```text
POST  /auth/logout
POST  /auth/logout-all
GET   /me
GET   /me/account-status
PATCH /me
POST  /me/avatar
```

### Approved student endpoints

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

### Administrator approval endpoints

```text
GET   /admin/users/pending
PATCH /admin/users/{user}/approve
PATCH /admin/users/{user}/reject
```

## Account Lifecycle

```text
register
  -> verify_phone
  -> pending admin approval
  -> approved OR rejected
```

Account states:

```text
pending
approved
rejected
```

After OTP verification, the API returns a Sanctum token even when the user is pending. This lets Flutter show the pending/rejected account screen and refresh `GET /me/account-status`.

Pending and rejected users cannot use learning, QR activation, lesson progress, or notification endpoints.

A blocked request returns:

```json
{
  "message": "Your account is pending administrator approval.",
  "accountStatus": "pending",
  "rejectionReason": null
}
```

Rejected example:

```json
{
  "message": "Your account was rejected by the administrator.",
  "accountStatus": "rejected",
  "rejectionReason": "Telegram account could not be verified."
}
```

## User Object

Every authentication, profile, and approval response uses these user properties:

```json
{
  "id": 1,
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

Telegram usernames are stored without the leading `@`.

## Demo Data

After running:

```bash
php artisan migrate:fresh --seed
```

Use:

```text
Approved student phone: +963999999999
Approved student Telegram: eduflow_student
Approved student password: password123

Admin phone: +963988888888
Admin Telegram: eduflow_admin
Admin password: password123

Demo QR/enrollment code: EDUFLOW-DEMO-2026
```

## Authentication

### POST `/auth/register`

Creates an unverified account with `accountStatus: pending` and generates a six-digit OTP.

Request:

```json
{
  "fullName": "EduFlow Student",
  "phone": "+963999999999",
  "telegramUsername": "@eduflow_student",
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

Telegram validation:

```text
required
3-64 characters
letters, numbers, and underscores only
unique between users
leading @ is removed automatically
```

Response: HTTP `201`

```json
{
  "user": {
    "id": 1,
    "name": "EduFlow Student",
    "phone": "+963999999999",
    "telegramUsername": "eduflow_student",
    "phoneVerifiedAt": null,
    "accountStatus": "pending"
  },
  "message": "Verification code sent successfully.",
  "nextAction": "verify_phone",
  "verificationExpiresAt": "2026-07-24T12:10:00.000000Z",
  "debugOtp": "123456"
}
```

`debugOtp` is returned only in `local` and `testing` environments. Connect an SMS gateway before production launch.

### POST `/auth/verify-otp`

Request:

```json
{
  "phone": "+963999999999",
  "otp": "123456",
  "deviceName": "Android phone"
}
```

Pending-account response:

```json
{
  "user": {
    "accountStatus": "pending",
    "rejectionReason": null
  },
  "token": "1|sanctum-token",
  "message": "Phone verified. Your account is pending administrator approval.",
  "nextAction": "await_admin_approval"
}
```

Flutter must save the token and open the pending approval screen rather than the main learning screen.

### POST `/auth/resend-otp`

Rate limited to three requests per minute.

```json
{
  "phone": "+963999999999"
}
```

Response includes:

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

The response always includes `user`, `token`, `message`, and `nextAction` for verified active users.

Possible `nextAction` values:

```text
open_app              account approved
await_admin_approval  account pending
show_rejection        account rejected
```

Pending example:

```json
{
  "user": {
    "accountStatus": "pending"
  },
  "token": "1|sanctum-token",
  "message": "Your account is pending administrator approval.",
  "nextAction": "await_admin_approval"
}
```

Rejected example:

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

### POST `/auth/logout`

Revokes the current token.

### POST `/auth/logout-all`

Revokes all user tokens.

## Profile and Account Status

### GET `/me`

Returns the authenticated user object.

### GET `/me/account-status`

Returns the same user resource and is intended for pending/rejected-screen refreshes.

Flutter behavior:

```text
pending  -> keep pending screen
approved -> navigate to main application
rejected -> show rejectionReason
```

### PATCH `/me`

Request properties:

```json
{
  "fullName": "Updated Student",
  "phone": "+963944444444",
  "telegramUsername": "@updated_student",
  "bio": "Learning with EduFlow",
  "locale": "ar",
  "timezone": "Asia/Damascus"
}
```

Accepted aliases:

```text
full_name -> fullName
name -> name
telegram_username -> telegramUsername
```

### POST `/me/avatar`

Multipart form-data:

```text
avatar: <image>
```

## Administrator Account Review

All routes require a Sanctum token belonging to a user with `isAdmin: true`.

### GET `/admin/users/pending`

Query parameters:

```text
perPage
search
```

`search` matches name, phone, or Telegram username.

Response is a paginated collection of pending user resources.

### PATCH `/admin/users/{user}/approve`

No request body is required.

Response:

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

Request:

```json
{
  "rejectionReason": "Telegram account could not be verified."
}
```

`rejection_reason` is accepted as an alias.

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

## Profile Learning Summary

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

## Public Catalog

Public catalog endpoints return metadata only. They never return paid lesson media or assessment answers.

### GET `/catalog/categories`

Returns the active category tree.

Category properties:

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

Course list properties include `coverUrl` and `introVideoUrl` when media exists.

### GET `/catalog/courses/{course}`

Returns safe public metadata. Modules, paid lessons, media URLs, and assessment answers are not loaded.

## Course Activation

### POST `/enrollment-codes/redeem`

Requires an approved account and is rate limited.

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

Redemption is transactional and idempotent for the same user/code combination.

## My Courses

### GET `/me/enrollments`

Returns paginated enrollments ordered by recent access.

Enrollment status is returned as `expired` when `expiresAt` is in the past.

### GET `/me/enrollments/{enrollment}`

Returns only an enrollment owned by the authenticated user.

### GET `/me/courses/{course}`

Returns the personalized course-learning payload used by Flutter course details, downloads, and the video player.

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

Returns `404` when no enrollment exists and `403` when course access has expired.

## Lesson Progress

### PATCH `/lessons/{lesson}/progress`

Requires an approved account with an active, non-expired course enrollment.

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

Behavior:

- Playback position is clamped between zero and lesson duration.
- Reaching lesson duration marks the lesson completed.
- Enrollment progress and last-accessed time are synchronized.
- There is no separate lesson-complete endpoint; send `status: completed` here.

## Notifications

All notification routes require an approved account.

### GET `/notifications`

Returns paginated notifications owned by the authenticated user.

### PATCH `/notifications/{notification}/read`

Returns the notification in a `data` wrapper.

### PATCH `/notifications/read-all`

Marks all authenticated-user notifications as read.

### GET `/me/notification-preferences`

Returns the current user notification preferences.

### PATCH `/me/notification-preferences`

Updates the current user notification preferences.

## Flutter Navigation Rules

After registration:

```text
register success -> OTP screen
```

After OTP verification or login:

```text
nextAction = open_app              -> main screen
nextAction = await_admin_approval  -> pending approval screen
nextAction = show_rejection        -> rejection screen with rejectionReason
```

During splash:

```text
stored token -> GET /me/account-status
approved     -> main screen
pending      -> pending screen
rejected     -> rejection screen
401          -> clear token and login screen
```

On any approved-only endpoint returning `403`:

```text
accountStatus = pending  -> pending screen
accountStatus = rejected -> rejection screen
```

## Removed Mobile Routes

The following groups are deliberately absent from the Flutter mobile API:

- Course/category/module/lesson management CRUD
- Course and lesson media-management routes
- Assessment authoring, question management, attempts, results, and grading
- Notification-template CRUD
- Learning/reporting/export routes
- Direct unaudited course-enrollment routes

Administrative content management should remain separate from the student mobile API.
