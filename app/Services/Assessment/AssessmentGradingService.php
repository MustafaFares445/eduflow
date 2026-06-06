<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Enums\Assessment\AttemptStatus;
use App\Enums\Assessment\QuestionType;
use App\Models\AssessmentAttempt;
use App\Models\AttemptAnswer;
use App\Models\Question;

final class AssessmentGradingService
{
    public function gradeAttempt(AssessmentAttempt $assessmentAttempt): AssessmentAttempt
    {
        $assessmentAttempt->loadMissing([
            'assessment.questions.options',
            'answers.question.options',
            'answers.selectedOption',
        ]);

        $questions = $assessmentAttempt->assessment->questions->where('is_active', true);
        $maxScore = (float) $questions->sum('points');
        $score = 0.0;

        foreach ($questions as $question) {
            $answer = $assessmentAttempt->answers->firstWhere('question_id', $question->id);
            if (! $answer) {
                continue;
            }

            [$isCorrect, $answerScore] = $this->gradeQuestion($question, $answer);

            $answer->forceFill([
                'is_correct' => $isCorrect,
                'score' => $answerScore,
                'graded_at' => now(),
            ])->save();

            $score += $answerScore;
        }

        $percentage = $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : 0.0;

        $assessmentAttempt->forceFill([
            'status' => AttemptStatus::Submitted,
            'submitted_at' => $assessmentAttempt->submitted_at ?? now(),
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'is_passed' => $percentage >= (float) $assessmentAttempt->assessment->passing_score,
            'graded_at' => now(),
        ])->save();

        return $assessmentAttempt->fresh([
            'assessment.course.category',
            'assessment.lesson.courseModule',
            'assessment.questions.options',
            'user',
            'answers.question.options',
            'answers.selectedOption',
        ]);
    }

    /**
     * @return array{0: bool, 1: float}
     */
    private function gradeQuestion(Question $question, AttemptAnswer $answer): array
    {
        $questionType = $question->type?->value ?? $question->type;
        if ($questionType === QuestionType::FreeText->value) {
            return [false, 0.0];
        }

        $correctOption = $question->options->firstWhere('is_correct', true);
        if ($correctOption === null) {
            return [false, 0.0];
        }

        $isCorrect = (int) $answer->selected_option_id === (int) $correctOption->id;

        return [$isCorrect, $isCorrect ? (float) $question->points : 0.0];
    }
}
