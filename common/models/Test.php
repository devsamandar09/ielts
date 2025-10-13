<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * Test model
 *
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string $description
 * @property string $difficulty
 * @property int $duration
 * @property int $total_questions
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_at
 */
class Test extends ActiveRecord
{
    const TYPE_LISTENING = 'listening';
    const TYPE_READING = 'reading';

    const DIFFICULTY_EASY = 'easy';
    const DIFFICULTY_MEDIUM = 'medium';
    const DIFFICULTY_HARD = 'hard';

    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%test}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'title'], 'required'],
            [['type'], 'in', 'range' => [self::TYPE_LISTENING, self::TYPE_READING]],
            [['difficulty'], 'in', 'range' => [self::DIFFICULTY_EASY, self::DIFFICULTY_MEDIUM, self::DIFFICULTY_HARD]],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_ARCHIVED]],
            [['description'], 'string'],
            [['duration', 'total_questions', 'status', 'created_by'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Test Type',
            'title' => 'Title',
            'description' => 'Description',
            'difficulty' => 'Difficulty',
            'duration' => 'Duration (minutes)',
            'total_questions' => 'Total Questions',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    // Relations

    public function getCreator()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getListeningSections()
    {
        return $this->hasMany(ListeningSection::class, ['test_id' => 'id'])
            ->orderBy(['section_number' => SORT_ASC]);
    }

    public function getReadingPassages()
    {
        return $this->hasMany(ReadingPassage::class, ['test_id' => 'id'])
            ->orderBy(['passage_number' => SORT_ASC]);
    }

    public function getQuestions()
    {
        return $this->hasMany(Question::class, ['test_id' => 'id'])
            ->orderBy(['order' => SORT_ASC]);
    }

    public function getAttempts()
    {
        return $this->hasMany(TestAttempt::class, ['test_id' => 'id']);
    }

    // Helper methods

    public function isListening()
    {
        return $this->type === self::TYPE_LISTENING;
    }

    public function isReading()
    {
        return $this->type === self::TYPE_READING;
    }

    public function isPublished()
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_ARCHIVED => 'Archived',
        ];
        return $labels[$this->status] ?? 'Unknown';
    }

    public function getDifficultyLabel()
    {
        $labels = [
            self::DIFFICULTY_EASY => 'Easy',
            self::DIFFICULTY_MEDIUM => 'Medium',
            self::DIFFICULTY_HARD => 'Hard',
        ];
        return $labels[$this->difficulty] ?? 'Unknown';
    }

    public function getTypeLabel()
    {
        return ucfirst($this->type);
    }

    /**
     * Update total questions count
     */
    public function updateQuestionsCount()
    {
        $this->total_questions = $this->getQuestions()->count();
        $this->save(false);
    }

    /**
     * Get average score for this test
     */
    public function getAverageScore()
    {
        return TestAttempt::find()
            ->where(['test_id' => $this->id, 'status' => TestAttempt::STATUS_COMPLETED])
            ->average('score');
    }

    /**
     * Get total attempts count
     */
    public function getTotalAttempts()
    {
        return TestAttempt::find()
            ->where(['test_id' => $this->id])
            ->count();
    }

    /**
     * Get completed attempts count
     */
    public function getCompletedAttempts()
    {
        return TestAttempt::find()
            ->where(['test_id' => $this->id, 'status' => TestAttempt::STATUS_COMPLETED])
            ->count();
    }

    /**
     * Publish test
     */
    public function publish()
    {
        $this->status = self::STATUS_PUBLISHED;
        return $this->save(false);
    }

    /**
     * Archive test
     */
    public function archive()
    {
        $this->status = self::STATUS_ARCHIVED;
        return $this->save(false);
    }

    /**
     * Get scoped query for published tests
     */
    public static function findPublished()
    {
        return static::find()->where(['status' => self::STATUS_PUBLISHED]);
    }
}


