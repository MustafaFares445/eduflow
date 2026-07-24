<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('does not expose assessment management or grading routes to mobile users', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/assessments', [])->assertNotFound();
    $this->postJson('/api/v1/questions', [])->assertNotFound();
    $this->postJson('/api/v1/assessments/1/attempts', [])->assertNotFound();
    $this->postJson('/api/v1/assessment-attempts/1/grade', [])->assertNotFound();
});

it('does not expose management and reporting routes to mobile users', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/courses', [])->assertNotFound();
    $this->postJson('/api/v1/course-categories', [])->assertNotFound();
    $this->getJson('/api/v1/reports/learning/overview')->assertNotFound();
    $this->getJson('/api/v1/notification-templates')->assertNotFound();
});
