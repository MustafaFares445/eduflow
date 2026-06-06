<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Assessment;

use App\Data\Assessment\QuestionOptionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Assessment\QuestionOptionRequest;
use App\Http\Resources\Api\V1\Assessment\QuestionOptionResource;
use App\Models\QuestionOption;
use App\Services\Assessment\QuestionOptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class QuestionOptionController extends Controller
{
    public function __construct(
        private readonly QuestionOptionService $questionOptionService,
    ) {}

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return QuestionOptionResource::collection(
            $this->questionOptionService->index(
                $request->all(),
                (int) $request->integer('perPage', 20),
                $request->string('search')->toString() ?: null
            )
        );
    }

    public function store(QuestionOptionRequest $request): QuestionOptionResource
    {
        return QuestionOptionResource::make(
            $this->questionOptionService->store(QuestionOptionData::from($request->validated()))
        );
    }

    public function show(QuestionOption $question_option): QuestionOptionResource
    {
        return QuestionOptionResource::make($this->questionOptionService->show($question_option));
    }

    public function update(QuestionOptionRequest $request, QuestionOption $question_option): QuestionOptionResource
    {
        return QuestionOptionResource::make(
            $this->questionOptionService->update($question_option, QuestionOptionData::from($request->validated()))
        );
    }

    public function destroy(QuestionOption $question_option): JsonResponse
    {
        $this->questionOptionService->delete($question_option);

        return response()->json(null, 204);
    }
}
