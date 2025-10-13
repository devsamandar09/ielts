<?php
namespace common\services\checkers;

use common\services\QuestionChecker;
use common\models\Question;

class FormCompletionChecker extends QuestionChecker
{
    public function check(Question $question, $userAnswer)
    {
        $correctAnswerData = $question->getCorrectAnswerData();

        if (!isset($correctAnswerData['fields'])) {
            return false;
        }

        $fields = $correctAnswerData['fields'];

        // User answer should be an array of field answers
        if (!is_array($userAnswer)) {
            return false;
        }

        $allCorrect = true;

        foreach ($fields as $index => $field) {
            if (!isset($userAnswer[$index])) {
                $allCorrect = false;
                continue;
            }

            $userFieldAnswer = $userAnswer[$index];
            $acceptableAnswers = $field['acceptable_answers'] ?? [$field['correct_answer']];

            $isFieldCorrect = false;
            foreach ($acceptableAnswers as $acceptable) {
                if ($this->normalizeAnswer($userFieldAnswer) === $this->normalizeAnswer($acceptable)) {
                    $isFieldCorrect = true;
                    break;
                }
            }

            if (!$isFieldCorrect) {
                $allCorrect = false;
            }
        }

        return $allCorrect;
    }
}
