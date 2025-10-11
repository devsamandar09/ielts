<?php

namespace common\services;

use common\models\Question;

abstract class QuestionChecker
{
    /**
     * Check if the user's answer is correct
     *
     * @param Question $question
     * @param mixed $userAnswer
     * @return bool
     */
    abstract public function check(Question $question, $userAnswer);

    /**
     * Create checker instance based on question type
     */
    public static function create($type)
    {
        $className = 'common\\services\\checkers\\' . self::getCheckerClassName($type);

        if (!class_exists($className)) {
            throw new \Exception("Checker for type {$type} not found");
        }

        return new $className();
    }

    /**
     * Convert question type to checker class name
     */
    private static function getCheckerClassName($type)
    {
        // multiple_choice -> MultipleChoiceChecker
        $parts = explode('_', $type);
        $parts = array_map('ucfirst', $parts);
        return implode('', $parts) . 'Checker';
    }

    /**
     * Normalize answer for comparison
     */
    protected function normalizeAnswer($answer)
    {
        if (is_array($answer)) {
            return array_map(function($item) {
                return $this->normalizeText($item);
            }, $answer);
        }
        return $this->normalizeText($answer);
    }

    /**
     * Normalize text
     */
    protected function normalizeText($text)
    {
        $text = trim($text);
        $text = strtolower($text);
        $text = preg_replace('/\s+/', ' ', $text);
        return $text;
    }

    /**
     * Check if two answers are similar (for text answers)
     */
    protected function isSimilar($answer1, $answer2, $threshold = 0.8)
    {
        $answer1 = $this->normalizeText($answer1);
        $answer2 = $this->normalizeText($answer2);

        if ($answer1 === $answer2) {
            return true;
        }

        // Calculate similarity
        similar_text($answer1, $answer2, $percent);
        return $percent >= ($threshold * 100);
    }
}
