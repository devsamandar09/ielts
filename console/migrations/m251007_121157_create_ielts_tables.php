<?php

use yii\db\Migration;

class m251007_121157_create_ielts_tables extends Migration
{
    /**
     * {@inheritdoc}
     */    public function safeUp()
{
    // Tests table
    $this->createTable('{{%test}}', [
        'id' => $this->primaryKey(),
        'type' => $this->string(20)->notNull()->comment('listening or reading'),
        'title' => $this->string(255)->notNull(),
        'description' => $this->text(),
        'difficulty' => $this->string(20)->defaultValue('medium'),
        'duration' => $this->integer()->comment('minutes'),
        'total_questions' => $this->integer()->defaultValue(0),
        'status' => $this->integer()->defaultValue(0)->comment('0=draft, 1=published, 2=archived'),
        'created_by' => $this->integer(),
        'created_at' => $this->integer(),
        'updated_at' => $this->integer(),
    ]);

    // Listening sections
    $this->createTable('{{%listening_section}}', [
        'id' => $this->primaryKey(),
        'test_id' => $this->integer()->notNull(),
        'section_number' => $this->integer()->notNull()->comment('1-4'),
        'title' => $this->string(255),
        'audio_url' => $this->string(500),
        'audio_duration' => $this->integer()->comment('seconds'),
        'context' => $this->text(),
        'transcript' => $this->text(),
        'created_at' => $this->integer(),
    ]);

    // Reading passages
    $this->createTable('{{%reading_passage}}', [
        'id' => $this->primaryKey(),
        'test_id' => $this->integer()->notNull(),
        'passage_number' => $this->integer()->notNull()->comment('1-3'),
        'title' => $this->string(255),
        'text' => $this->text()->notNull(),
        'word_count' => $this->integer(),
        'created_at' => $this->integer(),
    ]);

    // Questions
    $this->createTable('{{%question}}', [
        'id' => $this->primaryKey(),
        'test_id' => $this->integer()->notNull(),
        'section_id' => $this->integer()->comment('for listening'),
        'passage_id' => $this->integer()->comment('for reading'),
        'question_number' => $this->integer()->notNull(),
        'type' => $this->string(50)->notNull(),
        'question_text' => $this->text(),
        'instruction' => $this->text(),
        'question_data' => $this->text()->comment('JSON format'),
        'correct_answer' => $this->text()->comment('JSON format'),
        'explanation' => $this->text(),
        'points' => $this->integer()->defaultValue(1),
        'order' => $this->integer(),
    ]);

    // Test attempts
    $this->createTable('{{%test_attempt}}', [
        'id' => $this->primaryKey(),
        'user_id' => $this->integer()->notNull(),
        'test_id' => $this->integer()->notNull(),
        'started_at' => $this->integer(),
        'completed_at' => $this->integer(),
        'time_spent' => $this->integer()->comment('seconds'),
        'score' => $this->decimal(5, 2),
        'total_correct' => $this->integer()->defaultValue(0),
        'total_questions' => $this->integer()->defaultValue(0),
        'band_score' => $this->decimal(3, 1)->comment('IELTS band 1-9'),
        'status' => $this->string(20)->defaultValue('in_progress'),
    ]);

    // User answers
    $this->createTable('{{%user_answer}}', [
        'id' => $this->primaryKey(),
        'attempt_id' => $this->integer()->notNull(),
        'question_id' => $this->integer()->notNull(),
        'user_answer' => $this->text()->comment('JSON format'),
        'is_correct' => $this->boolean()->defaultValue(0),
        'points_earned' => $this->integer()->defaultValue(0),
        'answered_at' => $this->integer(),
    ]);

    // User progress (for dashboard statistics)
    $this->createTable('{{%user_progress}}', [
        'id' => $this->primaryKey(),
        'user_id' => $this->integer()->notNull(),
        'test_type' => $this->string(20)->comment('listening or reading'),
        'total_tests_taken' => $this->integer()->defaultValue(0),
        'average_score' => $this->decimal(5, 2),
        'average_band_score' => $this->decimal(3, 1),
        'best_score' => $this->decimal(5, 2),
        'best_band_score' => $this->decimal(3, 1),
        'total_time_spent' => $this->integer()->defaultValue(0)->comment('seconds'),
        'updated_at' => $this->integer(),
    ]);

    // Indexes
    $this->createIndex('idx-test-type', '{{%test}}', 'type');
    $this->createIndex('idx-test-status', '{{%test}}', 'status');
    $this->createIndex('idx-test-difficulty', '{{%test}}', 'difficulty');

    $this->createIndex('idx-listening_section-test_id', '{{%listening_section}}', 'test_id');
    $this->createIndex('idx-reading_passage-test_id', '{{%reading_passage}}', 'test_id');

    $this->createIndex('idx-question-test_id', '{{%question}}', 'test_id');
    $this->createIndex('idx-question-type', '{{%question}}', 'type');

    $this->createIndex('idx-test_attempt-user_id', '{{%test_attempt}}', 'user_id');
    $this->createIndex('idx-test_attempt-test_id', '{{%test_attempt}}', 'test_id');
    $this->createIndex('idx-test_attempt-status', '{{%test_attempt}}', 'status');

    $this->createIndex('idx-user_answer-attempt_id', '{{%user_answer}}', 'attempt_id');
    $this->createIndex('idx-user_answer-question_id', '{{%user_answer}}', 'question_id');

    $this->createIndex('idx-user_progress-user_id', '{{%user_progress}}', 'user_id');
    $this->createIndex('idx-user_progress-test_type', '{{%user_progress}}', 'test_type');

    // Foreign keys
    $this->addForeignKey(
        'fk-listening_section-test_id',
        '{{%listening_section}}',
        'test_id',
        '{{%test}}',
        'id',
        'CASCADE'
    );

    $this->addForeignKey(
        'fk-reading_passage-test_id',
        '{{%reading_passage}}',
        'test_id',
        '{{%test}}',
        'id',
        'CASCADE'
    );

    $this->addForeignKey(
        'fk-question-test_id',
        '{{%question}}',
        'test_id',
        '{{%test}}',
        'id',
        'CASCADE'
    );

    $this->addForeignKey(
        'fk-test_attempt-user_id',
        '{{%test_attempt}}',
        'user_id',
        '{{%user}}',
        'id',
        'CASCADE'
    );

    $this->addForeignKey(
        'fk-test_attempt-test_id',
        '{{%test_attempt}}',
        'test_id',
        '{{%test}}',
        'id',
        'CASCADE'
    );

    $this->addForeignKey(
        'fk-user_answer-attempt_id',
        '{{%user_answer}}',
        'attempt_id',
        '{{%test_attempt}}',
        'id',
        'CASCADE'
    );

    $this->addForeignKey(
        'fk-user_answer-question_id',
        '{{%user_answer}}',
        'question_id',
        '{{%question}}',
        'id',
        'CASCADE'
    );

    $this->addForeignKey(
        'fk-user_progress-user_id',
        '{{%user_progress}}',
        'user_id',
        '{{%user}}',
        'id',
        'CASCADE'
    );
}

    public function safeDown()
    {
        $this->dropForeignKey('fk-user_progress-user_id', '{{%user_progress}}');
        $this->dropForeignKey('fk-user_answer-question_id', '{{%user_answer}}');
        $this->dropForeignKey('fk-user_answer-attempt_id', '{{%user_answer}}');
        $this->dropForeignKey('fk-test_attempt-test_id', '{{%test_attempt}}');
        $this->dropForeignKey('fk-test_attempt-user_id', '{{%test_attempt}}');
        $this->dropForeignKey('fk-question-test_id', '{{%question}}');
        $this->dropForeignKey('fk-reading_passage-test_id', '{{%reading_passage}}');
        $this->dropForeignKey('fk-listening_section-test_id', '{{%listening_section}}');

        $this->dropTable('{{%user_progress}}');
        $this->dropTable('{{%user_answer}}');
        $this->dropTable('{{%test_attempt}}');
        $this->dropTable('{{%question}}');
        $this->dropTable('{{%reading_passage}}');
        $this->dropTable('{{%listening_section}}');
        $this->dropTable('{{%test}}');
    }


}
