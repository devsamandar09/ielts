<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * TestAttempt model
 *
 * @property int $id
 * @property int $user_id
 * @property int $test_id
 * @property int $started_at
 * @property int $completed_at
 * @property int $time_spent
 * @property float $score
 * @property int $total_correct
 * @property int $total_questions
 * @property float $band_score
 * @property string $status
 */
class TestAttempt extends ActiveRecord
{
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ABANDONED = 'abandoned';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%test_attempt}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'started_at',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'test_id'], 'required'],
            [['user_id', 'test_id', 'started_at', 'completed_at', 'time_spent', 'total_correct', 'total_questions'], 'integer'],
            [['score', 'band_score'], 'number'],
            [['status'], 'string', 'max' => 20],
            [['status'], 'default', 'value' => self::STATUS_IN_PROGRESS],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'test_id' => 'Test ID',
            'started_at' => 'Started At',
            'completed_at' => 'Completed At',
            'time_spent' => 'Time Spent',
            'score' => 'Score',
            'total_correct' => 'Total Correct',
            'total_questions' => 'Total Questions',
            'band_score' => 'Band Score',
            'status' => 'Status',
        ];
    }

    // Relations

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getTest()
    {
        return $this->hasOne(Test::class, ['id' => 'test_id']);
    }

    public function getUserAnswers()
    {
        return $this->hasMany(UserAnswer::class, ['attempt_id' => 'id']);
    }

    // Static methods

    /**
     * Start a new test attempt
     */
    public static function startTest($userId, $testId)
    {
        // Check if there's an unfinished attempt
        $existing = self::find()
            ->where([
                'user_id' => $userId,
                'test_id' => $testId,
                'status' => self::STATUS_IN_PROGRESS
            ])
            ->one();

        if ($existing) {
            return $existing;
        }

        $attempt = new self();
        $attempt->user_id = $userId;
        $attempt->test_id = $testId;
        $attempt->status = self::STATUS_IN_PROGRESS;
        $attempt->started_at = time();

        if ($attempt->save()) {
            return $attempt;
        }

        return null;
    }

    // Instance methods

    /**
     * Complete the test
     */
    public function completeTest()
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = time();
        $this->time_spent = $this->completed_at - $this->started_at;

        // Calculate score
        $this->calculateScore();

        // Update user progress
        $this->updateUserProgress();

        return $this->save(false);
    }

    /**
     * Calculate test score
     */
    private function calculateScore()
    {
        $totalPoints = 0;
        $earnedPoints = 0;
        $totalCorrect = 0;

        $questions = $this->test->questions;
        $this->total_questions = count($questions);

        foreach ($questions as $question) {
            $totalPoints += $question->points;

            $userAnswer = UserAnswer::findOne([
                'attempt_id' => $this->id,
                'question_id' => $question->id,
            ]);

            if ($userAnswer && $userAnswer->is_correct) {
                $earnedPoints += $userAnswer->points_earned;
                $totalCorrect++;
            }
        }

        $this->total_correct = $totalCorrect;

        // Calculate percentage score
        $this->score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

        // Calculate IELTS band score
        $this->band_score = $this->calculateBandScore($this->total_correct, $this->total_questions);
    }

    /**
     * Calculate IELTS band score based on correct answers
     */
    private function calculateBandScore($correct, $total)
    {
        if ($total == 0) return 0;

        $percentage = ($correct / $total) * 100;

        // IELTS band score mapping (simplified version)
        if ($percentage >= 90) return 9.0;
        if ($percentage >= 87) return 8.5;
        if ($percentage >= 82) return 8.0;
        if ($percentage >= 77) return 7.5;
        if ($percentage >= 70) return 7.0;
        if ($percentage >= 65) return 6.5;
        if ($percentage >= 60) return 6.0;
        if ($percentage >= 55) return 5.5;
        if ($percentage >= 50) return 5.0;
        if ($percentage >= 45) return 4.5;
        if ($percentage >= 40) return 4.0;
        if ($percentage >= 35) return 3.5;
        if ($percentage >= 30) return 3.0;
        return 2.5;
    }

    /**
     * Update user progress statistics
     */
    private function updateUserProgress()
    {
        $progress = UserProgress::findOne([
            'user_id' => $this->user_id,
            'test_type' => $this->test->type,
        ]);

        if (!$progress) {
            $progress = new UserProgress();
            $progress->user_id = $this->user_id;
            $progress->test_type = $this->test->type;
            $progress->total_tests_taken = 0;
            $progress->total_time_spent = 0;
        }

        $progress->total_tests_taken++;
        $progress->total_time_spent += $this->time_spent;

        // Calculate average scores
        $completedAttempts = self::find()
            ->where([
                'user_id' => $this->user_id,
                'status' => self::STATUS_COMPLETED
            ])
            ->joinWith('test')
            ->andWhere(['test.type' => $this->test->type])
            ->all();

        $totalScore = 0;
        $totalBandScore = 0;
        $count = count($completedAttempts);

        foreach ($completedAttempts as $attempt) {
            $totalScore += $attempt->score;
            $totalBandScore += $attempt->band_score;
        }

        $progress->average_score = $count > 0 ? round($totalScore / $count, 2) : 0;
        $progress->average_band_score = $count > 0 ? round($totalBandScore / $count, 1) : 0;

        // Update best scores
        if (!$progress->best_score || $this->score > $progress->best_score) {
            $progress->best_score = $this->score;
        }

        if (!$progress->best_band_score || $this->band_score > $progress->best_band_score) {
            $progress->best_band_score = $this->band_score;
        }

        $progress->updated_at = time();
        $progress->save(false);
    }

    /**
     * Abandon the test
     */
    public function abandonTest()
    {
        $this->status = self::STATUS_ABANDONED;
        return $this->save(false);
    }

    // Helper methods

    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_ABANDONED => 'Abandoned',
        ];
        return $labels[$this->status] ?? 'Unknown';
    }

    public function getTimeSpentFormatted()
    {
        $hours = floor($this->time_spent / 3600);
        $minutes = floor(($this->time_spent % 3600) / 60);
        $seconds = $this->time_spent % 60;

        if ($hours > 0) {
            return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        }
        return sprintf("%02d:%02d", $minutes, $seconds);
    }

    public function getScorePercentage()
    {
        return round($this->score, 0) . '%';
    }

    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
