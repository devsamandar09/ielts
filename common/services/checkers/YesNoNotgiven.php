<?php

namespace common\services\checkers;

use common\services\QuestionChecker;
use common\models\Question;

class YesNoNotgivenChecker extends QuestionChecker
{
    public function check(Question $question, $userAnswer)
    {
        $correctAnswer = $question->getCorrectAnswerData();

        $normalized = $this->normalizeAnswer($userAnswer);
        $correct = $this->normalizeAnswer($correctAnswer);

        $acceptableAnswers = [
            'yes' => ['yes', 'y'],
            'no' => ['no', 'n'],
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
