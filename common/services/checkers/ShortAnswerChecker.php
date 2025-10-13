<?php
namespace common\services\checkers;

use common\services\QuestionChecker;
use common\models\Question;

class ShortAnswerChecker extends QuestionChecker
{
    public function check(Question $question, $userAnswer)
    {
        $correctAnswerData = $question->getCorrectAnswerData();

        $acceptableAnswers = $correctAnswerData['acceptable_answers'] ?? [$correctAnswerData];

        foreach ($acceptableAnswers as $acceptable) {
            if ($this->normalizeAnswer($userAnswer) === $this->normalizeAnswer($acceptable)) {
                return true;
            }
        }

        return false;
    }
}



