<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Data\Assessment\QuestionOptionData;
use App\Models\QuestionOption;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

final class QuestionOptionService
{
    public function index(array $filters = [], int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        $query = QueryBuilder::for(QuestionOption::query()->with(['question.assessment']))
            ->allowedFilters(
                AllowedFilter::exact('questionId', 'question_id'),
                AllowedFilter::exact('isCorrect', 'is_correct'),
            )
            ->allowedSorts(
                AllowedSort::field('position'),
                AllowedSort::field('created_at'),
            );

        if ($search !== null && $search !== '') {
            $query->where('option_text', 'like', '%'.$search.'%');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function show(QuestionOption $questionOption): QuestionOption
    {
        return $questionOption->load(['question.assessment']);
    }

    public function store(QuestionOptionData $data): QuestionOption
    {
        $questionOption = new QuestionOption();
        $questionOption->fill([
            'question_id' => $data->questionId,
            'option_text' => $data->optionText,
            'is_correct' => $data->isCorrect,
            'position' => $data->position,
        ]);
        $questionOption->save();

        return $questionOption->fresh(['question.assessment']);
    }

    public function update(QuestionOption $questionOption, QuestionOptionData $data): QuestionOption
    {
        $questionOption->fill([
            'question_id' => $data->questionId,
            'option_text' => $data->optionText,
            'is_correct' => $data->isCorrect,
            'position' => $data->position,
        ]);
        $questionOption->save();

        return $questionOption->fresh(['question.assessment']);
    }

    public function delete(QuestionOption $questionOption): void
    {
        $questionOption->delete();
    }
}
