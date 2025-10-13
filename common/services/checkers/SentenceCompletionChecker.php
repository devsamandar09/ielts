<?php

namespace common\services\checkers;

use common\services\QuestionChecker;
use common\models\Question;

class SentenceCompletionChecker extends QuestionChecker
{
    public function check(Question $question, $userAnswer)
    {
        $correctAnswerData = $question->getCorrectAnswerData();

        // Can have multiple acceptable answers
        if (isset($correctAnswerData['acceptable_answers'])) {
            $acceptableAnswers = $correctAnswerData['acceptable_answers'];

            foreach ($acceptableAnswers as $acceptable) {
                if ($this->normalizeAnswer($userAnswer) === $this->normalizeAnswer($acceptable)) {
                    return true;
                }
            }
            return false;
        }

        // Single answer
        $correctAnswer = $correctAnswerData['answer'] ?? $correctAnswerData;
        return $this->normalizeAnswer($userAnswer) === $this->normalizeAnswer($correctAnswer);
    }
}
