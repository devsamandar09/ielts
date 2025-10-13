<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * ReadingPassage model
 *
 * @property int $id
 * @property int $test_id
 * @property int $passage_number
 * @property string $title
 * @property string $text
 * @property int $word_count
 * @property int $created_at
 */
class ReadingPassage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%reading_passage}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
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
            [['test_id', 'passage_number', 'text'], 'required'],
            [['test_id', 'passage_number', 'word_count', 'created_at'], 'integer'],
            [['text'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['passage_number'], 'in', 'range' => [1, 2, 3]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'test_id' => 'Test ID',
            'passage_number' => 'Passage Number',
            'title' => 'Title',
            'text' => 'Text',
            'word_count' => 'Word Count',
            'created_at' => 'Created At',
        ];
    }

    // Relations

    public function getTest()
    {
        return $this->hasOne(Test::class, ['id' => 'test_id']);
    }

    public function getQuestions()
    {
        return $this->hasMany(Question::class, ['passage_id' => 'id'])
            ->orderBy(['order' => SORT_ASC]);
    }

    // Helper methods

    public function getPassageTitle()
    {
        return $this->title ?: "Passage {$this->passage_number}";
    }

    public function getEstimatedReadingTime()
    {
        // Average reading speed: 200 words per minute
        return ceil($this->word_count / 200);
    }

    /**
     * Before save - calculate word count
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->text) {
                $this->word_count = str_word_count(strip_tags($this->text));
            }
            return true;
        }
        return false;
    }
}
