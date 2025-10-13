<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class UserProgress extends ActiveRecord
{

    /**
     * UserProgress model
     *
     * @property int $id
     * @property int $user_id
     * @property string $test_type
     * @property int $total_tests_taken
     * @property float $average_score
     * @property float $average_band_score
     * @property float $best_score
     * @property float $best_band_score
     * @property int $total_time_spent
     * @property int $updated_at
     */

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_progress}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => false,
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'test_type'], 'required'],
            [['user_id', 'total_tests_taken', 'total_time_spent', 'updated_at'], 'integer'],
            [['average_score', 'average_band_score', 'best_score', 'best_band_score'], 'number'],
            [['test_type'], 'string', 'max' => 20],
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
            'test_type' => 'Test Type',
            'total_tests_taken' => 'Total Tests Taken',
            'average_score' => 'Average Score',
            'average_band_score' => 'Average Band Score',
            'best_score' => 'Best Score',
            'best_band_score' => 'Best Band Score',
            'total_time_spent' => 'Total Time Spent',
            'updated_at' => 'Updated At',
        ];
    }

    // Relations

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    // Helper methods

    public function getTotalTimeSpentFormatted()
    {
        $hours = floor($this->total_time_spent / 3600);
        $minutes = floor(($this->total_time_spent % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }

    public function getTestTypeLabel()
    {
        return ucfirst($this->test_type);
    }

    /**
     * Get progress for a specific user and test type
     */
    public static function getProgress($userId, $testType)
    {
        return self::findOne([
            'user_id' => $userId,
            'test_type' => $testType,
        ]);
    }


}