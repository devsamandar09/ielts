<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * UserAnswer model
 *
 * @property int $id
 * @property int $attempt_id
 * @property int $question_id
 * @property string $user_answer
 * @property int $is_correct
 * @property int $points_earned
 * @property int $answered_at
 */
class UserAnswer extends ActiveRecord
{
    private $_userAnswerData;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_answer}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'answered_at',
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
            [['attempt_id', 'question_id'], 'required'],
            [['attempt_id', 'question_id', 'is_correct', 'points_earned', 'answered_at'], 'integer'],
            [['user_answer'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'attempt_id' => 'Attempt ID',
            'question_id' => 'Question ID',
            'user_answer' => 'User Answer',
            'is_correct' => 'Is Correct',
            'points_earned' => 'Points Earned',
            'answered_at' => 'Answered At',
        ];
    }

    // Relations

    public function getAttempt()
    {
        return $this->hasOne(TestAttempt::class, ['id' => 'attempt_id']);
    }

    public function getQuestion()
    {
        return $this->hasOne(Question::class, ['id' => 'question_id']);
    }

    // JSON data methods

    public function getUserAnswerData()
    {
        if ($this->_userAnswerData === null && $this->user_answer) {
            $this->_userAnswerData = Json::decode($this->user_answer);
        }
        return $this->_userAnswerData;
    }

    public function setUserAnswerData($value)
    {
        $this->_userAnswerData = $value;
        $this->user_answer = Json::encode($value);
    }

    /**
     * Save user answer and check if it's correct
     */
    public static function saveAnswer($attemptId, $questionId, $answer)
    {
        $userAnswer = self::findOne([
            'attempt_id' => $attemptId,
            'question_id' => $questionId,
        ]);

        if (!$userAnswer) {
            $userAnswer = new self();
            $userAnswer->attempt_id = $attemptId;
            $userAnswer->question_id = $questionId;
        }

        $userAnswer->setUserAnswerData($answer);
        $userAnswer->answered_at = time();

        // Check if answer is correct
        $question = Question::findOne($questionId);
        if ($question) {
            $isCorrect = $question->checkAnswer($answer);
            $userAnswer->is_correct = $isCorrect ? 1 : 0;
            $userAnswer->points_earned = $isCorrect ? $question->points : 0;
        }

        return $userAnswer->save();
    }
}
