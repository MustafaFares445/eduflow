# EduFlow Mobile API Contract

Base URL: `/api/v1`

Authentication: Laravel Sanctum bearer tokens.

This contract is intentionally limited to the Flutter student application. Content-management, reporting, notification-template, assessment-authoring, grading, and administrative CRUD routes are not exposed through the mobile API.

## Conventions

- Request and response properties use camelCase.
- Registration also accepts the existing Flutter alias `full_name`.
- Protected routes require:

```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

- Collection responses use Laravel pagination with `data`, `links`, and `meta`.
- Validation failures use HTTP `422`.
- Missing or inaccessible records use HTTP `404`.
- Expired course access uses HTTP `403`.

## Demo Data

After running `php artisan migrate:fresh --seed`:

- Student phone: `+963999999999`
- Student password: `password123`
- Instructor phone: `+963988888888`
- Instructor password: `password123`
- Demo QR/enrollment code: `EDUFLOW-DEMO-2026`

## Authentication

### POST `/auth/register`

Creates an unverified phone account and sends/generates a six-digit OTP.

```json
{
  "fullName": "EduFlow Student",
  "phone": "+963999999999",
  "password": "password123",
  "locale": "ar",
  "timezone": "Asia/Damascus"
}
```

`full_name` is accepted as an alias for `fullName`.

```json
{
  "user": {
    "id": 1,
    "name": "EduFlow Student",
    "email": null,
    "phone": "+963999999999",
    "phoneVerifiedAt": null,
    "avatarUrl": null
  },
  "message": "Verification code sent successfully.",
  "verificationExpiresAt": "2026-07-24T12:10:00.000000Z",
  "debugOtp": "123456"
}
```

`debugOtp` is returned only in `local` and `testing` environments. Connect an SMS provider before production launch.

### POST `/auth/verify-otp`

```json
{
  "phone": "+963999999999",
  "otp": "123456",
  "deviceName": "Android phone"
}
```

```json
{
  "user": {},
  "token": "1|sanctum-token",
  "message": "Phone verified successfully."
}
```

### POST `/auth/resend-otp`

Rate limited to three requests per minute.

```json
{
  "phone": "+963999999999"
}
```

### POST `/auth/login`

```json
{
  "phone": "+963999999999",
  "password": "password123",
  "deviceName": "Android phone"
}
```

Returns `user`, `token`, and `message`. Login is rejected when the account is unverified or inactive.

### POST `/auth/logout`

Revokes the current token.

### POST `/auth/logout-all`

Revokes all user tokens.

## Profile

### GET `/me`

Returns the authenticated user.

### PATCH `/me`

```json
{
  "fullName": "Updated Student",
  "phone": "+963944444444",
  "bio": "Learning with EduFlow",
  "locale": "ar",
  "timezone": "Asia/Damascus"
}
```

`full_name` and `name` are also accepted.

### POST `/me/avatar`

Multipart form-data:

```text
avatar: <image>
```

### GET `/me/learning-summary`

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

Category properties include:

```text
id, parentId, name, slug, description, icon, color, imageUrl,
isActive, sortOrder, coursesCount, children
```

### GET `/catalog/courses`

Supported query parameters:

```text
perPage
search
filter[categoryId]
filter[level]
sort
```

Course list properties include `coverUrl` and `introVideoUrl` when media exists.

### GET `/catalog/courses/{course}`

Returns safe public course metadata. `modules`, `lessons`, and assessments are not loaded here.

## Course Activation

### POST `/enrollment-codes/redeem`

Protected and rate limited.

```json
{
  "code": "EDUFLOW-DEMO-2026"
}
```

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

Returns the personalized course-learning payload used by the Flutter details and player screens.

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

The endpoint returns `404` when no enrollment exists and `403` when access has expired.

## Lesson Progress

### PATCH `/lessons/{lesson}/progress`

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

- Requires an existing non-expired enrollment.
- Progress is clamped between zero and lesson duration.
- Reaching lesson duration marks the lesson completed.
- The enrollment aggregate progress and last-accessed time are synchronized.

The former dedicated `/complete` endpoint was removed; send `status: completed` through this endpoint.

## Notifications

### GET `/notifications`

Returns paginated notifications owned by the authenticated user.

### PATCH `/notifications/{notification}/read`

Returns the notification in a `data` wrapper.

### PATCH `/notifications/read-all`

Marks all authenticated-user notifications as read.

### GET `/me/notification-preferences`

### PATCH `/me/notification-preferences`

## Removed Mobile Routes

The following groups are deliberately absent from `/api/v1`:

- Course/category/module/lesson management CRUD
- Course and lesson media-management routes
- Assessment, question, option, attempt, result, and grading routes
- Notification-template CRUD
- Learning/reporting/export routes
- Direct unaudited course-enrollment route

Management functionality should be implemented in an authenticated admin surface rather than mixed into the student API.
