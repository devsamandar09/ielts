<?php
namespace common\services\checkers;

use common\services\QuestionChecker;
use common\models\Question;

class MatchingChecker extends QuestionChecker
{
    public function check(Question $question, $userAnswer)
    {
        $correctAnswerData = $question->getCorrectAnswerData();

        if (!isset($correctAnswerData['matches'])) {
            return false;
        }

        $matches = $correctAnswerData['matches'];

        // User answer should be array of matches
        if (!is_array($userAnswer)) {
            return false;
        }

        $allCorrect = true;

        foreach ($matches as $index => $match) {
            if (!isset($userAnswer[$index])) {
                $allCorrect = false;
                continue;
            }

            if ($this->normalizeAnswer($userAnswer[$index]) !== $this->normalizeAnswer($match)) {
                $allCorrect = false;
            }
        }

        return $allCorrect;
    }
}
