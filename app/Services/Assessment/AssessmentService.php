<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Data\Assessment\AssessmentData;
use App\Models\Assessment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

final class AssessmentService
{
    public function index(array $filters = [], int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        $query = QueryBuilder::for(Assessment::query()->with([
            'course.category',
            'lesson.courseModule',
            'questions.options',
            'attempts.user',
        ]))
            ->allowedFilters(
                AllowedFilter::exact('courseId', 'course_id'),
                AllowedFilter::exact('lessonId', 'lesson_id'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('status'),
            )
            ->allowedSorts(
                AllowedSort::field('title'),
                AllowedSort::field('position'),
                AllowedSort::field('created_at'),
            );

        if ($search !== null && $search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function show(Assessment $assessment): Assessment
    {
        return $assessment->load([
            'course.category',
            'lesson.courseModule',
            'questions.options',
            'attempts.user',
        ]);
    }

    public function store(AssessmentData $data): Assessment
    {
        $assessment = new Assessment();
        $assessment->fill([
            'course_id' => $data->courseId,
            'lesson_id' => $data->lessonId,
            'title' => $data->title,
            'description' => $data->description,
            'type' => $data->type,
            'status' => $data->status,
            'passing_score' => $data->passingScore,
            'max_attempts' => $data->maxAttempts,
            'time_limit_minutes' => $data->timeLimitMinutes,
            'shuffle_questions' => $data->shuffleQuestions,
            'show_result_immediately' => $data->showResultImmediately,
            'position' => $data->position,
        ]);
        $assessment->save();

        return $assessment->fresh([
            'course.category',
            'lesson.courseModule',
            'questions.options',
            'attempts.user',
        ]);
    }

    public function update(Assessment $assessment, AssessmentData $data): Assessment
    {
        $assessment->fill([
            'course_id' => $data->courseId,
            'lesson_id' => $data->lessonId,
            'title' => $data->title,
            'description' => $data->description,
            'type' => $data->type,
            'status' => $data->status,
            'passing_score' => $data->passingScore,
            'max_attempts' => $data->maxAttempts,
            'time_limit_minutes' => $data->timeLimitMinutes,
            'shuffle_questions' => $data->shuffleQuestions,
            'show_result_immediately' => $data->showResultImmediately,
            'position' => $data->position,
        ]);
        $assessment->save();

        return $assessment->fresh([
            'course.category',
            'lesson.courseModule',
            'questions.options',
            'attempts.user',
        ]);
    }

    public function publish(Assessment $assessment): Assessment
    {
        $assessment->forceFill(['status' => 'published'])->save();

        return $assessment->fresh([
            'course.category',
            'lesson.courseModule',
            'questions.options',
            'attempts.user',
        ]);
    }

    public function archive(Assessment $assessment): Assessment
    {
        $assessment->forceFill(['status' => 'archived'])->save();

        return $assessment->fresh([
            'course.category',
            'lesson.courseModule',
            'questions.options',
            'attempts.user',
        ]);
    }

    public function delete(Assessment $assessment): void
    {
        $assessment->delete();
    }
}
