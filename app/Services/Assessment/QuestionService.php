<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Data\Assessment\QuestionData;
use App\Models\Question;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

final class QuestionService
{
    public function index(array $filters = [], int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        $query = QueryBuilder::for(Question::query()->with(['assessment.course.category', 'options']))
            ->allowedFilters(
                AllowedFilter::exact('assessmentId', 'assessment_id'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('isActive', 'is_active'),
            )
            ->allowedSorts(
                AllowedSort::field('position'),
                AllowedSort::field('created_at'),
            );

        if ($search !== null && $search !== '') {
            $query->where('question_text', 'like', '%'.$search.'%');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function show(Question $question): Question
    {
        return $question->load(['assessment.course.category', 'options']);
    }

    public function store(QuestionData $data): Question
    {
        $question = new Question();
        $question->fill([
            'assessment_id' => $data->assessmentId,
            'type' => $data->type,
            'question_text' => $data->questionText,
            'explanation' => $data->explanation,
            'points' => $data->points,
            'position' => $data->position,
            'is_active' => $data->isActive,
        ]);
        $question->save();

        return $question->fresh(['assessment.course.category', 'options']);
    }

    public function update(Question $question, QuestionData $data): Question
    {
        $question->fill([
            'assessment_id' => $data->assessmentId,
            'type' => $data->type,
            'question_text' => $data->questionText,
            'explanation' => $data->explanation,
            'points' => $data->points,
            'position' => $data->position,
            'is_active' => $data->isActive,
        ]);
        $question->save();

        return $question->fresh(['assessment.course.category', 'options']);
    }

    /**
     * @param array<int, array{id:int, position:int}> $items
     */
    public function reorder(array $items): void
    {
        DB::transaction(function () use ($items): void {
            foreach ($items as $item) {
                Question::query()
                    ->whereKey($item['id'])
                    ->update(['position' => $item['position']]);
            }
        });
    }

    public function delete(Question $question): void
    {
        $question->delete();
    }
}
