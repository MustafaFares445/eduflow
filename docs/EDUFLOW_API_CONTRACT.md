# EduFlow Learning App API Contract

Base URL: `/api/v1`

Auth: Sanctum token auth for protected routes.

Scope note:
- Authentication is implemented.
- Authorization is intentionally out of scope for this pass.
- OTP and QR are UI-only for this pass.

## Conventions

- Request field names use camelCase.
- Relation identifiers in responses use camelCase suffixes like `categoryId`, `courseId`, `lessonId`.
- Most single-resource responses are returned as Laravel JSON resources.
- Most collection responses are wrapped in `data` with standard Laravel pagination `links` and `meta`.
- Decimal casts are serialized as strings, for example `"0.00"` and `"5.00"`.

## Authentication Headers

For protected requests:

```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

## Standard Validation Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

## Demo Credentials

Seeder data is available through `DatabaseSeeder`.

- Instructor: `instructor@eduflow.local` / `password123`
- Student: `student@eduflow.local` / `password123`

## Demo Content

The demo seeder creates:

- Category tree:
  - Academic Year 1
  - Mathematics
- Course:
  - Foundations of Maths
- Module:
  - Numbers and Sets
- Lesson:
  - Introduction to Numbers
- Assessment:
  - Demo Quiz
- Question:
  - Which command installs the API scaffolding?
- Notification template:
  - `course.published`

## Common Response Shapes

### Single resource

```json
{
  "data": {
    "id": 1
  }
}
```

### Paginated collection

```json
{
  "data": [],
  "links": {
    "first": "http://localhost/api/v1/courses?page=1",
    "last": "http://localhost/api/v1/courses?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost/api/v1/courses",
    "per_page": 20,
    "to": 1,
    "total": 1
  }
}
```

## Auth

### POST `/auth/register`

Create a new user and return a Sanctum token.

Request:

```json
{
  "name": "EduFlow Student",
  "email": "student@example.com",
  "password": "password123",
  "phone": "+963999999999",
  "locale": "en",
  "timezone": "Asia/Damascus"
}
```

Response:

```json
{
  "user": {
    "id": 1,
    "name": "EduFlow Student",
    "email": "student@example.com",
    "phone": "+963999999999",
    "bio": null,
    "timezone": "Asia/Damascus",
    "locale": "en",
    "isActive": true,
    "lastLoginAt": "2026-06-06T18:00:00.000000Z",
    "avatarUrl": null,
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  },
  "token": "1|eduflow-personal-access-token"
}
```

### POST `/auth/login`

Request:

```json
{
  "email": "student@eduflow.local",
  "password": "password123",
  "deviceName": "iPhone 15"
}
```

Response:

```json
{
  "user": {
    "id": 2,
    "name": "EduFlow Student",
    "email": "student@eduflow.local",
    "phone": null,
    "bio": null,
    "timezone": "Asia/Damascus",
    "locale": "en",
    "isActive": true,
    "lastLoginAt": "2026-06-06T18:00:00.000000Z",
    "avatarUrl": null,
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  },
  "token": "2|eduflow-personal-access-token"
}
```

### POST `/auth/logout`

No request body.

Response:

```json
{
  "message": "Logged out successfully."
}
```

### POST `/auth/logout-all`

No request body.

Response:

```json
{
  "message": "Logged out from all devices."
}
```

### GET `/me`

Response:

```json
{
  "data": {
    "id": 2,
    "name": "EduFlow Student",
    "email": "student@eduflow.local",
    "phone": null,
    "bio": null,
    "timezone": "Asia/Damascus",
    "locale": "en",
    "isActive": true,
    "lastLoginAt": "2026-06-06T18:00:00.000000Z",
    "avatarUrl": null,
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  }
}
```

### PATCH `/me`

Request:

```json
{
  "name": "Updated Student",
  "phone": "+963944444444",
  "bio": "Learning with EduFlow",
  "timezone": "Asia/Damascus",
  "locale": "ar"
}
```

Response:

```json
{
  "data": {
    "id": 2,
    "name": "Updated Student",
    "email": "student@eduflow.local",
    "phone": "+963944444444",
    "bio": "Learning with EduFlow",
    "timezone": "Asia/Damascus",
    "locale": "ar",
    "isActive": true,
    "lastLoginAt": "2026-06-06T18:00:00.000000Z",
    "avatarUrl": null,
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  }
}
```

### POST `/me/avatar`

Multipart form-data:

```text
avatar: <file>
```

Response:

```json
{
  "data": {
    "id": 2,
    "name": "Updated Student",
    "email": "student@eduflow.local",
    "phone": "+963944444444",
    "bio": "Learning with EduFlow",
    "timezone": "Asia/Damascus",
    "locale": "ar",
    "isActive": true,
    "lastLoginAt": "2026-06-06T18:00:00.000000Z",
    "avatarUrl": "http://localhost/storage/1/avatar.jpg",
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  }
}
```

## Catalog

### GET `/catalog/categories`

Returns the active category tree.

Response:

```json
{
  "data": [
    {
      "id": 1,
      "parentId": null,
      "name": "Academic Year 1",
      "slug": "academic-year-1",
      "description": "Primary academic year",
      "icon": null,
      "color": null,
      "isActive": true,
      "sortOrder": 1,
      "parent": null,
      "children": [
        {
          "id": 2,
          "parentId": 1,
          "name": "Mathematics",
          "slug": "mathematics",
          "description": "Core math subject",
          "icon": null,
          "color": null,
          "isActive": true,
          "sortOrder": 1,
          "parent": null,
          "children": [],
          "coursesCount": 1,
          "createdAt": "2026-06-06T18:00:00.000000Z",
          "updatedAt": "2026-06-06T18:00:00.000000Z"
        }
      ],
      "coursesCount": 0,
      "createdAt": "2026-06-06T18:00:00.000000Z",
      "updatedAt": "2026-06-06T18:00:00.000000Z"
    }
  ]
}
```

### GET `/catalog/courses`

Query params:

```text
?perPage=20&search=math&filter[categoryId]=2&filter[level]=beginner&sort=title
```

Response:

```json
{
  "data": [
    {
      "id": 1,
      "categoryId": 2,
      "instructorId": 1,
      "title": "Foundations of Maths",
      "slug": "foundations-of-maths",
      "shortDescription": "A beginner course for core math skills.",
      "description": "This demo course powers the EduFlow sample content.",
      "level": "beginner",
      "language": "en",
      "status": "published",
      "visibility": "public",
      "durationMinutes": 7,
      "lessonsCount": 1,
      "studentsCount": 1,
      "averageRating": "0.00",
      "ratingsCount": 0,
      "publishedAt": "2026-06-06T18:00:00.000000Z",
      "category": {
        "id": 2,
        "parentId": 1,
        "name": "Mathematics",
        "slug": "mathematics",
        "description": "Core math subject",
        "icon": null,
        "color": null,
        "isActive": true,
        "sortOrder": 1,
        "parent": {
          "id": 1,
          "parentId": null,
          "name": "Academic Year 1",
          "slug": "academic-year-1",
          "description": "Primary academic year",
          "icon": null,
          "color": null,
          "isActive": true,
          "sortOrder": 1,
          "parent": null,
          "children": null,
          "coursesCount": 0,
          "createdAt": "2026-06-06T18:00:00.000000Z",
          "updatedAt": "2026-06-06T18:00:00.000000Z"
        },
        "children": [],
        "coursesCount": 1,
        "createdAt": "2026-06-06T18:00:00.000000Z",
        "updatedAt": "2026-06-06T18:00:00.000000Z"
      },
      "instructor": {
        "id": 1,
        "name": "EduFlow Instructor",
        "email": "instructor@eduflow.local",
        "phone": null,
        "bio": null,
        "timezone": "Asia/Damascus",
        "locale": "en",
        "isActive": true,
        "lastLoginAt": "2026-06-06T18:00:00.000000Z",
        "avatarUrl": null,
        "createdAt": "2026-06-06T18:00:00.000000Z",
        "updatedAt": "2026-06-06T18:00:00.000000Z"
      },
      "modules": null,
      "lessons": null,
      "assessments": null,
      "reviewsCount": 0,
      "cover": [],
      "introVideo": [],
      "attachments": [],
      "createdAt": "2026-06-06T18:00:00.000000Z",
      "updatedAt": "2026-06-06T18:00:00.000000Z"
    }
  ]
}
```

### GET `/catalog/courses/{course}`

Response includes the full course tree.

```json
{
  "data": {
    "id": 1,
    "categoryId": 2,
    "instructorId": 1,
    "title": "Foundations of Maths",
    "slug": "foundations-of-maths",
    "shortDescription": "A beginner course for core math skills.",
    "description": "This demo course powers the EduFlow sample content.",
    "level": "beginner",
    "language": "en",
    "status": "published",
    "visibility": "public",
    "durationMinutes": 7,
    "lessonsCount": 1,
    "studentsCount": 1,
    "averageRating": "0.00",
    "ratingsCount": 0,
    "publishedAt": "2026-06-06T18:00:00.000000Z",
    "category": { "...": "see category resource" },
    "instructor": { "...": "see user resource" },
    "modules": [
      {
        "id": 1,
        "courseId": 1,
        "title": "Numbers and Sets",
        "description": "Module one overview",
        "position": 1,
        "isActive": true,
        "course": null,
        "lessons": [
          {
            "id": 1,
            "courseId": 1,
            "courseModuleId": 1,
            "title": "Introduction to Numbers",
            "slug": "introduction-to-numbers",
            "type": "video",
            "body": "Demo lesson body",
            "durationSeconds": 420,
            "position": 1,
            "isPreview": true,
            "isActive": true,
            "publishedAt": "2026-06-06T18:00:00.000000Z",
            "course": null,
            "courseModule": null,
            "assessment": {
              "id": 1,
              "courseId": 1,
              "lessonId": 1,
              "title": "Demo Quiz",
              "description": "Sample assessment for the demo course",
              "type": "quiz",
              "status": "published",
              "passingScore": 60,
              "maxAttempts": 3,
              "timeLimitMinutes": 15,
              "shuffleQuestions": false,
              "showResultImmediately": true,
              "position": 1,
              "course": null,
              "lesson": null,
              "questions": [],
              "attempts": [],
              "createdAt": "2026-06-06T18:00:00.000000Z",
              "updatedAt": "2026-06-06T18:00:00.000000Z"
            },
            "video": [],
            "files": [],
            "images": [],
            "createdAt": "2026-06-06T18:00:00.000000Z",
            "updatedAt": "2026-06-06T18:00:00.000000Z"
          }
        ],
        "createdAt": "2026-06-06T18:00:00.000000Z",
        "updatedAt": "2026-06-06T18:00:00.000000Z"
      }
    ],
    "lessons": null,
    "assessments": null,
    "reviewsCount": 0,
    "cover": [],
    "introVideo": [],
    "attachments": [],
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  }
}
```

### GET `/catalog/courses/{course}/modules`

```json
{
  "data": [
    {
      "id": 1,
      "courseId": 1,
      "title": "Numbers and Sets",
      "description": "Module one overview",
      "position": 1,
      "isActive": true,
      "course": { "...": "course resource" },
      "lessons": [
        {
          "id": 1,
          "courseId": 1,
          "courseModuleId": 1,
          "title": "Introduction to Numbers",
          "slug": "introduction-to-numbers",
          "type": "video",
          "body": "Demo lesson body",
          "durationSeconds": 420,
          "position": 1,
          "isPreview": true,
          "isActive": true,
          "publishedAt": "2026-06-06T18:00:00.000000Z",
          "course": null,
          "courseModule": null,
          "assessment": null,
          "video": [],
          "files": [],
          "images": [],
          "createdAt": "2026-06-06T18:00:00.000000Z",
          "updatedAt": "2026-06-06T18:00:00.000000Z"
        }
      ],
      "createdAt": "2026-06-06T18:00:00.000000Z",
      "updatedAt": "2026-06-06T18:00:00.000000Z"
    }
  ]
}
```

### GET `/catalog/lessons/{lesson}`

```json
{
  "data": {
    "id": 1,
    "courseId": 1,
    "courseModuleId": 1,
    "title": "Introduction to Numbers",
    "slug": "introduction-to-numbers",
    "type": "video",
    "body": "Demo lesson body",
    "durationSeconds": 420,
    "position": 1,
    "isPreview": true,
    "isActive": true,
    "publishedAt": "2026-06-06T18:00:00.000000Z",
    "course": { "...": "course resource" },
    "courseModule": { "...": "module resource" },
    "assessment": { "...": "assessment resource" },
    "video": [],
    "files": [],
    "images": [],
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  }
}
```

## Learning Management

### Course Categories

#### POST `/course-categories`

Request:

```json
{
  "parentId": 1,
  "name": "Physics",
  "description": "Science subject",
  "icon": "atom",
  "color": "#00A3FF",
  "isActive": true,
  "sortOrder": 2
}
```

Response: `CourseCategoryResource`

#### PUT `/course-categories/{course_category}`

Same body as create.

#### DELETE `/course-categories/{course_category}`

No body.

### Courses

#### POST `/courses`

Request:

```json
{
  "categoryId": 2,
  "instructorId": 1,
  "title": "Functions and Graphs",
  "shortDescription": "Learn how functions work.",
  "description": "Full course body.",
  "level": "beginner",
  "language": "en",
  "status": "draft",
  "visibility": "public",
  "publishedAt": "2026-06-06T18:00:00.000000Z"
}
```

#### PUT `/courses/{course}`

Same body as create.

#### POST `/courses/{course}/publish`

No body.

#### POST `/courses/{course}/archive`

No body.

#### POST `/courses/{course}/media`

Multipart form-data:

```text
cover: <image file>
introVideo: <video file>
attachments[]: <file>
attachments[]: <file>
```

Response:

```json
{
  "data": {
    "id": 1,
    "categoryId": 2,
    "instructorId": 1,
    "title": "Foundations of Maths",
    "slug": "foundations-of-maths",
    "shortDescription": "A beginner course for core math skills.",
    "description": "This demo course powers the EduFlow sample content.",
    "level": "beginner",
    "language": "en",
    "status": "published",
    "visibility": "public",
    "durationMinutes": 7,
    "lessonsCount": 1,
    "studentsCount": 1,
    "averageRating": "0.00",
    "ratingsCount": 0,
    "publishedAt": "2026-06-06T18:00:00.000000Z",
    "category": { "...": "category resource" },
    "instructor": { "...": "user resource" },
    "modules": null,
    "lessons": null,
    "assessments": null,
    "reviewsCount": 0,
    "cover": [
      {
        "id": 1,
        "name": "cover",
        "fileName": "cover.jpg",
        "mimeType": "image/jpeg",
        "collectionName": "cover",
        "url": "http://localhost/storage/1/cover.jpg",
        "size": 123456,
        "createdAt": "2026-06-06T18:00:00.000000Z"
      }
    ],
    "introVideo": [],
    "attachments": [],
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  }
}
```

#### DELETE `/courses/{course}/media/{media}`

No body.

### Course Modules

#### POST `/course-modules`

Request:

```json
{
  "courseId": 1,
  "title": "Fractions",
  "description": "Module about fractions",
  "position": 2,
  "isActive": true
}
```

#### PUT `/course-modules/{course_module}`

Same body as create.

#### POST `/course-modules/reorder`

Request:

```json
{
  "items": [
    { "id": 1, "position": 1 },
    { "id": 2, "position": 2 }
  ]
}
```

#### DELETE `/course-modules/{course_module}`

No body.

### Lessons

#### POST `/lessons`

Request:

```json
{
  "courseId": 1,
  "courseModuleId": 1,
  "title": "Introduction to Fractions",
  "type": "video",
  "body": "Lesson body.",
  "durationSeconds": 420,
  "position": 1,
  "isPreview": true,
  "isActive": true,
  "publishedAt": "2026-06-06T18:00:00.000000Z"
}
```

#### PUT `/lessons/{lesson}`

Same body as create.

#### POST `/lessons/reorder`

Request:

```json
{
  "items": [
    { "id": 1, "position": 1 },
    { "id": 2, "position": 2 }
  ]
}
```

#### POST `/lessons/{lesson}/media`

Multipart form-data:

```text
video: <video file>
files[]: <file>
files[]: <file>
images[]: <image file>
```

#### DELETE `/lessons/{lesson}/media/{media}`

No body.

### Enrollment and Progress

#### POST `/courses/{course}/enroll`

No request body.

Response:

```json
{
  "data": {
    "id": 1,
    "userId": 2,
    "courseId": 1,
    "status": "active",
    "progressPercentage": "0.00",
    "enrolledAt": "2026-06-06T18:00:00.000000Z",
    "completedAt": null,
    "lastAccessedAt": "2026-06-06T18:00:00.000000Z",
    "user": { "...": "user resource" },
    "course": { "...": "course resource" },
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  }
}
```

#### GET `/me/enrollments`

Response:

```json
{
  "data": [
    {
      "id": 1,
      "userId": 2,
      "courseId": 1,
      "status": "completed",
      "progressPercentage": "100.00",
      "enrolledAt": "2026-06-06T18:00:00.000000Z",
      "completedAt": "2026-06-06T18:00:00.000000Z",
      "lastAccessedAt": "2026-06-06T18:00:00.000000Z",
      "user": { "...": "user resource" },
      "course": { "...": "course resource" },
      "createdAt": "2026-06-06T18:00:00.000000Z",
      "updatedAt": "2026-06-06T18:00:00.000000Z"
    }
  ]
}
```

#### GET `/me/enrollments/{enrollment}`

Response:

```json
{
  "data": {
    "id": 1,
    "userId": 2,
    "courseId": 1,
    "status": "completed",
    "progressPercentage": "100.00",
    "enrolledAt": "2026-06-06T18:00:00.000000Z",
    "completedAt": "2026-06-06T18:00:00.000000Z",
    "lastAccessedAt": "2026-06-06T18:00:00.000000Z",
    "user": { "...": "user resource" },
    "course": { "...": "course resource" },
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  }
}
```

#### PATCH `/lessons/{lesson}/progress`

Request:

```json
{
  "status": "completed",
  "progressSeconds": 420
}
```

Response:

```json
{
  "data": {
    "id": 1,
    "userId": 2,
    "courseId": 1,
    "lessonId": 1,
    "status": "completed",
    "progressSeconds": 420,
    "completedAt": "2026-06-06T18:00:00.000000Z",
    "lastAccessedAt": "2026-06-06T18:00:00.000000Z",
    "user": { "...": "user resource" },
    "course": { "...": "course resource" },
    "lesson": { "...": "lesson resource" },
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  }
}
```

#### POST `/lessons/{lesson}/complete`

No request body.

Response shape matches lesson progress resource.

## Assessments

### POST `/assessments`

Request:

```json
{
  "courseId": 1,
  "lessonId": 1,
  "title": "Demo Quiz",
  "description": "Sample assessment",
  "type": "quiz",
  "status": "draft",
  "passingScore": 60,
  "maxAttempts": 3,
  "timeLimitMinutes": 15,
  "shuffleQuestions": false,
  "showResultImmediately": true,
  "position": 1
}
```

### PUT `/assessments/{assessment}`

Same body as create.

### POST `/assessments/{assessment}/publish`

No body.

### POST `/assessments/{assessment}/archive`

No body.

### POST `/questions`

Request:

```json
{
  "assessmentId": 1,
  "type": "multiple_choice",
  "questionText": "Which command installs the API scaffolding?",
  "explanation": "Laravel 13 uses install:api.",
  "points": 5,
  "position": 1,
  "isActive": true
}
```

### PUT `/questions/{question}`

Same body as create.

### POST `/questions/reorder`

Request:

```json
{
  "items": [
    { "id": 1, "position": 1 },
    { "id": 2, "position": 2 }
  ]
}
```

### POST `/question-options`

Request:

```json
{
  "questionId": 1,
  "optionText": "php artisan install:api",
  "isCorrect": true,
  "position": 1
}
```

### PUT `/question-options/{question_option}`

Same body as create.

### POST `/assessments/{assessment}/attempts`

No body.

Response:

```json
{
  "data": {
    "id": 1,
    "assessmentId": 1,
    "userId": 2,
    "status": "started",
    "startedAt": "2026-06-06T18:00:00.000000Z",
    "submittedAt": null,
    "expiresAt": "2026-06-06T18:15:00.000000Z",
    "score": "0.00",
    "maxScore": "5.00",
    "percentage": "0.00",
    "isPassed": false,
    "attemptNumber": 1,
    "gradedAt": null,
    "assessment": { "...": "assessment resource" },
    "user": { "...": "user resource" },
    "answers": [],
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  }
}
```

### GET `/assessments/{assessment}/attempts`

Returns a paginated list of attempts for the assessment.

### GET `/attempts/{assessmentAttempt}`

Returns the attempt with assessment, user, and answers loaded.

### POST `/attempts/{assessmentAttempt}/answers`

Request:

```json
{
  "questionId": 1,
  "selectedOptionId": 1,
  "answerText": null,
  "answerJson": null
}
```

Response:

```json
{
  "data": {
    "id": 1,
    "assessmentAttemptId": 1,
    "questionId": 1,
    "selectedOptionId": 1,
    "answerText": null,
    "answerJson": null,
    "isCorrect": true,
    "score": "5.00",
    "gradedAt": "2026-06-06T18:00:00.000000Z",
    "question": { "...": "question resource" },
    "selectedOption": { "...": "question option resource" },
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  }
}
```

### POST `/attempts/{assessmentAttempt}/submit`

No body.

Response:

```json
{
  "data": {
    "id": 1,
    "assessmentId": 1,
    "userId": 2,
    "status": "submitted",
    "startedAt": "2026-06-06T18:00:00.000000Z",
    "submittedAt": "2026-06-06T18:00:00.000000Z",
    "expiresAt": "2026-06-06T18:15:00.000000Z",
    "score": "5.00",
    "maxScore": "5.00",
    "percentage": "100.00",
    "isPassed": true,
    "attemptNumber": 1,
    "gradedAt": "2026-06-06T18:00:00.000000Z",
    "assessment": { "...": "assessment resource" },
    "user": { "...": "user resource" },
    "answers": [
      {
        "id": 1,
        "assessmentAttemptId": 1,
        "questionId": 1,
        "selectedOptionId": 1,
        "answerText": null,
        "answerJson": null,
        "isCorrect": true,
        "score": "5.00",
        "gradedAt": "2026-06-06T18:00:00.000000Z",
        "question": { "...": "question resource" },
        "selectedOption": { "...": "question option resource" },
        "createdAt": "2026-06-06T18:00:00.000000Z",
        "updatedAt": "2026-06-06T18:00:00.000000Z"
      }
    ],
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  }
}
```

### GET `/attempts/{assessmentAttempt}/result`

Same shape as submit result.

### POST `/assessment-attempts/{assessmentAttempt}/grade`

Same shape as submit result.

## Notifications

### GET `/notifications`

Returns authenticated user notifications.

### PATCH `/notifications/{notification}/read`

This endpoint returns the notification object directly, without a `data` wrapper.

Response:

```json
{
  "id": "17b66854-fa7a-4e01-8c63-f4c892f229e5",
  "type": "course-updated",
  "data": {
    "title": "Course updated"
  },
  "readAt": "2026-06-06T18:00:00.000000Z",
  "createdAt": "2026-06-06T18:00:00.000000Z",
  "updatedAt": "2026-06-06T18:00:00.000000Z"
}
```

### PATCH `/notifications/read-all`

```json
{
  "message": "Notifications marked as read."
}
```

### GET `/me/notification-preferences`

Response:

```json
{
  "data": {
    "id": 1,
    "userId": 2,
    "emailEnabled": true,
    "pushEnabled": true,
    "inAppEnabled": true,
    "courseUpdatesEnabled": true,
    "assessmentUpdatesEnabled": true,
    "createdAt": "2026-06-06T18:00:00.000000Z",
    "updatedAt": "2026-06-06T18:00:00.000000Z"
  }
}
```

### PATCH `/me/notification-preferences`

Request:

```json
{
  "emailEnabled": true,
  "pushEnabled": false,
  "inAppEnabled": true,
  "courseUpdatesEnabled": true,
  "assessmentUpdatesEnabled": false
}
```

### GET `/notification-templates`

Paginated list of templates.

### POST `/notification-templates`

Request:

```json
{
  "key": "course.published",
  "title": "Course published",
  "body": "A new course is available in EduFlow.",
  "channels": ["database"],
  "variables": ["courseTitle"],
  "isActive": true
}
```

### PUT `/notification-templates/{notification_template}`

Same body as create.

### DELETE `/notification-templates/{notification_template}`

No body.

## Reports

### GET `/reports/learning/overview`

```json
{
  "coursesTotal": 1,
  "publishedCourses": 1,
  "categoriesTotal": 2,
  "enrollmentsTotal": 1,
  "activeEnrollments": 0,
  "completedLessons": 1,
  "assessmentsTotal": 1,
  "attemptsTotal": 1,
  "averageAssessmentScore": 100
}
```

### GET `/reports/courses/{course}`

```json
{
  "course": {
    "id": 1,
    "title": "Foundations of Maths",
    "status": "published",
    "visibility": "public",
    "lessonsCount": 1,
    "studentsCount": 1,
    "averageRating": "0.00",
    "ratingsCount": 0
  },
  "enrollments": {
    "total": 1,
    "active": 0,
    "completed": 1
  },
  "lessons": {
    "total": 1,
    "completed": 1
  },
  "assessments": {
    "total": 1,
    "attempts": 1
  }
}
```

### GET `/reports/users/{user}/progress`

```json
{
  "user": {
    "id": 2,
    "name": "EduFlow Student",
    "email": "student@eduflow.local"
  },
  "enrollmentsTotal": 1,
  "activeEnrollments": 0,
  "completedEnrollments": 1,
  "completedLessons": 1,
  "attemptsTotal": 1,
  "averageAssessmentScore": 100
}
```

### GET `/reports/assessments/{assessment}`

```json
{
  "assessment": {
    "id": 1,
    "title": "Demo Quiz",
    "status": "published",
    "passingScore": 60
  },
  "attemptsTotal": 1,
  "submittedAttempts": 1,
  "passRate": 100,
  "averageScore": 100,
  "maxAttempts": 3
}
```

### POST `/reports/learning/export`

```json
{
  "generatedAt": "2026-06-06T18:00:00.000000Z",
  "overview": {
    "coursesTotal": 1,
    "publishedCourses": 1,
    "categoriesTotal": 2,
    "enrollmentsTotal": 1,
    "activeEnrollments": 0,
    "completedLessons": 1,
    "assessmentsTotal": 1,
    "attemptsTotal": 1,
    "averageAssessmentScore": 100
  }
}
```

## Notes for Clients

- Use `Authorization: Bearer <token>` after login or register.
- For media endpoints, send `multipart/form-data`.
- For lists, use `perPage`, `search`, `filter[...]`, and `sort` where supported.
- No role-based behavior is available in this API contract.
