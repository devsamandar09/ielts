<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * ListeningSection model
 *
 * @property int $id
 * @property int $test_id
 * @property int $section_number
 * @property string $title
 * @property string $audio_url
 * @property int $audio_duration
 * @property string $context
 * @property string $transcript
 * @property int $created_at
 */
class ListeningSection extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%listening_section}}';
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
            [['test_id', 'section_number'], 'required'],
            [['test_id', 'section_number', 'audio_duration', 'created_at'], 'integer'],
            [['context', 'transcript'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['audio_url'], 'string', 'max' => 500],
            [['section_number'], 'in', 'range' => [1, 2, 3, 4]],
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
            'section_number' => 'Section Number',
            'title' => 'Title',
            'audio_url' => 'Audio URL',
            'audio_duration' => 'Audio Duration (seconds)',
            'context' => 'Context',
            'transcript' => 'Transcript',
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
        return $this->hasMany(Question::class, ['section_id' => 'id'])
            ->orderBy(['order' => SORT_ASC]);
    }

    // Helper methods

    public function getAudioDurationFormatted()
    {
        $minutes = floor($this->audio_duration / 60);
        $seconds = $this->audio_duration % 60;
        return sprintf("%02d:%02d", $minutes, $seconds);
    }

    public function getSectionTitle()
    {
        return $this->title ?: "Section {$this->section_number}";
    }
}
