<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Assessment;

use App\Data\Assessment\QuestionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Assessment\QuestionReorderRequest;
use App\Http\Requests\Api\V1\Assessment\QuestionRequest;
use App\Http\Resources\Api\V1\Assessment\QuestionResource;
use App\Models\Question;
use App\Services\Assessment\QuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class QuestionController extends Controller
{
    public function __construct(
        private readonly QuestionService $questionService,
    ) {}

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return QuestionResource::collection(
            $this->questionService->index(
                $request->all(),
                (int) $request->integer('perPage', 20),
                $request->string('search')->toString() ?: null
            )
        );
    }

    public function store(QuestionRequest $request): QuestionResource
    {
        return QuestionResource::make(
            $this->questionService->store(QuestionData::from($request->validated()))
        );
    }

    public function show(Question $question): QuestionResource
    {
        return QuestionResource::make($this->questionService->show($question));
    }

    public function update(QuestionRequest $request, Question $question): QuestionResource
    {
        return QuestionResource::make(
            $this->questionService->update($question, QuestionData::from($request->validated()))
        );
    }

    public function destroy(Question $question): JsonResponse
    {
        $this->questionService->delete($question);

        return response()->json(null, 204);
    }

    public function reorder(QuestionReorderRequest $request): JsonResponse
    {
        $this->questionService->reorder($request->validated('items'));

        return response()->json([
            'message' => __('Questions reordered successfully.'),
        ]);
    }
}
