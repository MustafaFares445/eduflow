<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Data\Assessment\AttemptAnswerData;
use App\Enums\Assessment\AttemptStatus;
use App\Enums\Assessment\QuestionType;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

final class AssessmentAttemptService
{
    public function __construct(
        private readonly AssessmentGradingService $gradingService,
    ) {}

    public function index(Assessment $assessment, int $perPage = 20): LengthAwarePaginator
    {
        return AssessmentAttempt::query()
            ->where('assessment_id', $assessment->id)
            ->with(['user', 'answers.question.options', 'answers.selectedOption'])
            ->latest('started_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function start(Assessment $assessment, User $user): AssessmentAttempt
    {
        $attemptCount = AssessmentAttempt::query()
            ->where('assessment_id', $assessment->id)
            ->where('user_id', $user->id)
            ->count();

        if ($assessment->max_attempts !== null && $attemptCount >= $assessment->max_attempts) {
            throw ValidationException::withMessages([
                'assessment' => __('Maximum attempts reached.'),
            ]);
        }

        $startedAt = now();
        $expiresAt = $assessment->time_limit_minutes !== null
            ? $startedAt->copy()->addMinutes($assessment->time_limit_minutes)
            : null;

        $attempt = new AssessmentAttempt();
        $attempt->fill([
            'assessment_id' => $assessment->id,
            'user_id' => $user->id,
            'status' => AttemptStatus::Started,
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
            'score' => 0,
            'max_score' => (float) $assessment->questions()->where('is_active', true)->sum('points'),
            'percentage' => 0,
            'is_passed' => false,
            'attempt_number' => $attemptCount + 1,
        ]);
        $attempt->save();

        return $attempt->fresh(['assessment.questions.options', 'user', 'answers.question.options', 'answers.selectedOption']);
    }

    public function show(AssessmentAttempt $assessmentAttempt): AssessmentAttempt
    {
        return $assessmentAttempt->load([
            'assessment.course.category',
            'assessment.lesson.courseModule',
            'assessment.questions.options',
            'user',
            'answers.question.options',
            'answers.selectedOption',
        ]);
    }

    public function saveAnswer(AssessmentAttempt $assessmentAttempt, AttemptAnswerData $data): AttemptAnswer
    {
        $question = Question::query()
            ->where('assessment_id', $assessmentAttempt->assessment_id)
            ->with('options')
            ->findOrFail($data->questionId);

        $selectedOption = null;
        if ($data->selectedOptionId !== null) {
            $selectedOption = QuestionOption::query()
                ->where('question_id', $question->id)
                ->find($data->selectedOptionId);

            if ($selectedOption === null) {
                throw ValidationException::withMessages([
                    'selectedOptionId' => __('The selected option is invalid for this question.'),
                ]);
            }
        }

        [$isCorrect, $score] = $this->evaluateAnswer($question, $selectedOption);

        $answer = AttemptAnswer::query()->updateOrCreate(
            [
                'assessment_attempt_id' => $assessmentAttempt->id,
                'question_id' => $question->id,
            ],
            [
                'selected_option_id' => $selectedOption?->id,
                'answer_text' => $data->answerText,
                'answer_json' => $data->answerJson,
                'is_correct' => $isCorrect,
                'score' => $score,
                'graded_at' => now(),
            ]
        );

        return $answer->fresh(['question.options', 'selectedOption']);
    }

    public function submit(AssessmentAttempt $assessmentAttempt): AssessmentAttempt
    {
        $assessmentAttempt->forceFill([
            'status' => AttemptStatus::Submitted,
            'submitted_at' => $assessmentAttempt->submitted_at ?? now(),
        ])->save();

        return $this->gradingService->gradeAttempt($assessmentAttempt);
    }

    public function result(AssessmentAttempt $assessmentAttempt): AssessmentAttempt
    {
        return $this->gradingService->gradeAttempt($assessmentAttempt);
    }

    public function grade(AssessmentAttempt $assessmentAttempt): AssessmentAttempt
    {
        return $this->gradingService->gradeAttempt($assessmentAttempt);
    }

    private function evaluateAnswer(Question $question, ?QuestionOption $selectedOption): array
    {
        $questionType = $question->type?->value ?? $question->type;

        if ($questionType === QuestionType::FreeText->value) {
            return [false, 0.0];
        }

        if ($selectedOption === null) {
            return [false, 0.0];
        }

        $isCorrect = (bool) $selectedOption->is_correct;

        return [$isCorrect, $isCorrect ? (float) $question->points : 0.0];
    }
}
