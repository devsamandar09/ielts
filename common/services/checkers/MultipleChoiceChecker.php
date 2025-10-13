<?php

namespace common\services\checkers;

use common\services\QuestionChecker;
use common\models\Question;

class MultipleChoiceChecker extends QuestionChecker
{
    public function check(Question $question, $userAnswer)
    {
        $correctAnswer = $question->getCorrectAnswerData();

        if (is_array($correctAnswer)) {
            // Multiple correct answers possible
            return in_array($this->normalizeAnswer($userAnswer), $this->normalizeAnswer($correctAnswer));
        }

        return $this->normalizeAnswer($userAnswer) === $this->normalizeAnswer($correctAnswer);
    }
}

