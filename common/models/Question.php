<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * Question model
 *
 * @property int $id
 * @property int $test_id
 * @property int $section_id
 * @property int $passage_id
 * @property int $question_number
 * @property string $type
 * @property string $question_text
 * @property string $instruction
 * @property string $question_data
 * @property string $correct_answer
 * @property string $explanation
 * @property int $points
 * @property int $order
 */
class Question extends ActiveRecord
{
    // Question types
    const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    const TYPE_TRUE_FALSE_NOTGIVEN = 'true_false_notgiven';
    const TYPE_YES_NO_NOTGIVEN = 'yes_no_notgiven';
    const TYPE_MATCHING = 'matching';
    const TYPE_MATCHING_HEADINGS = 'matching_headings';
    const TYPE_MATCHING_INFORMATION = 'matching_information';
    const TYPE_MATCHING_FEATURES = 'matching_features';
    const TYPE_SENTENCE_COMPLETION = 'sentence_completion';
    const TYPE_SUMMARY_COMPLETION = 'summary_completion';
    const TYPE_FORM_COMPLETION = 'form_completion';
    const TYPE_NOTE_COMPLETION = 'note_completion';
    const TYPE_TABLE_COMPLETION = 'table_completion';
    const TYPE_FLOWCHART_COMPLETION = 'flowchart_completion';
    const TYPE_DIAGRAM_LABELING = 'diagram_labeling';
    const TYPE_SHORT_ANSWER = 'short_answer';

    private $_questionData;
    private $_correctAnswer;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%question}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['test_id', 'question_number', 'type'], 'required'],
            [['test_id', 'section_id', 'passage_id', 'question_number', 'points', 'order'], 'integer'],
            [['question_text', 'instruction', 'question_data', 'correct_answer', 'explanation'], 'string'],
            [['type'], 'string', 'max' => 50],
            [['points'], 'default', 'value' => 1],
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
            'section_id' => 'Section ID',
            'passage_id' => 'Passage ID',
            'question_number' => 'Question Number',
            'type' => 'Type',
            'question_text' => 'Question Text',
            'instruction' => 'Instruction',
            'question_data' => 'Question Data',
            'correct_answer' => 'Correct Answer',
            'explanation' => 'Explanation',
            'points' => 'Points',
            'order' => 'Order',
        ];
    }

    // Relations

    public function getTest()
    {
        return $this->hasOne(Test::class, ['id' => 'test_id']);
    }

    public function getListeningSection()
    {
        return $this->hasOne(ListeningSection::class, ['id' => 'section_id']);
    }

    public function getReadingPassage()
    {
        return $this->hasOne(ReadingPassage::class, ['id' => 'passage_id']);
    }

    public function getUserAnswers()
    {
        return $this->hasMany(UserAnswer::class, ['question_id' => 'id']);
    }

    // JSON data getters/setters

    public function getQuestionData()
    {
        if ($this->_questionData === null && $this->question_data) {
            $this->_questionData = Json::decode($this->question_data);
        }
        return $this->_questionData ?: [];
    }

    public function setQuestionData($value)
    {
        $this->_questionData = $value;
        $this->question_data = Json::encode($value);
    }

    public function getCorrectAnswerData()
    {
        if ($this->_correctAnswer === null && $this->correct_answer) {
            $this->_correctAnswer = Json::decode($this->correct_answer);
        }
        return $this->_correctAnswer;
    }

    public function setCorrectAnswerData($value)
    {
        $this->_correctAnswer = $value;
        $this->correct_answer = Json::encode($value);
    }

    // Helper methods

    public function getTypeLabel()
    {
        $labels = [
            self::TYPE_MULTIPLE_CHOICE => 'Multiple Choice',
            self::TYPE_TRUE_FALSE_NOTGIVEN => 'True/False/Not Given',
            self::TYPE_YES_NO_NOTGIVEN => 'Yes/No/Not Given',
            self::TYPE_MATCHING => 'Matching',
            self::TYPE_MATCHING_HEADINGS => 'Matching Headings',
            self::TYPE_MATCHING_INFORMATION => 'Matching Information',
            self::TYPE_MATCHING_FEATURES => 'Matching Features',
            self::TYPE_SENTENCE_COMPLETION => 'Sentence Completion',
            self::TYPE_SUMMARY_COMPLETION => 'Summary Completion',
            self::TYPE_FORM_COMPLETION => 'Form Completion',
            self::TYPE_NOTE_COMPLETION => 'Note Completion',
            self::TYPE_TABLE_COMPLETION => 'Table Completion',
            self::TYPE_FLOWCHART_COMPLETION => 'Flowchart Completion',
            self::TYPE_DIAGRAM_LABELING => 'Diagram Labeling',
            self::TYPE_SHORT_ANSWER => 'Short Answer',
        ];
        return $labels[$this->type] ?? 'Unknown';
    }

    /**
     * Check if user answer is correct
     */
    public function checkAnswer($userAnswer)
    {
        $checker = \common\services\QuestionChecker::create($this->type);
        return $checker->check($this, $userAnswer);
    }
}
