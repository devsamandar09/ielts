<?php
namespace common\services\checkers;

use common\services\QuestionChecker;
use common\models\Question;

class TrueFalseNotgivenChecker extends QuestionChecker
{
    public function check(Question $question, $userAnswer)
    {
        $correctAnswer = $question->getCorrectAnswerData();

        $normalized = $this->normalizeAnswer($userAnswer);
        $correct = $this->normalizeAnswer($correctAnswer);

        // Accept variations like "T" for "TRUE", "F" for "FALSE", "NG" for "NOT GIVEN"
        $acceptableAnswers = [
            'true' => ['true', 't'],
            'false' => ['false', 'f'],
            'not given' => ['not given', 'ng', 'notgiven', 'not-given'],
        ];

        foreach ($acceptableAnswers as $key => $variations) {
            if (in_array($correct, $variations) && in_array($normalized, $variations)) {
                return true;
            }
        }

        return $normalized === $correct;
    }
}
