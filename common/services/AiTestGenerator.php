<?php
namespace common\services;

use Yii;
use common\models\Test;
use common\models\Question;
use common\models\ListeningSection;
use common\models\ReadingPassage;
use yii\helpers\Json;

class AiTestGenerator
{
    private $apiKey;
    private $openAiEndpoint = 'https://api.openai.com/v1/chat/completions';
    private $elevenLabsEndpoint = 'https://api.elevenlabs.io/v1/text-to-speech';

    public function __construct()
    {
        $this->apiKey = Yii::$app->params['openaiApiKey'];
    }

    /**
     * Generate complete IELTS test
     */
    public function generateTest($type, $difficulty = 'medium', $topic = null, $userId = null)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Create test record
            $test = new Test();
            $test->type = $type;
            $test->difficulty = $difficulty;
            $test->status = Test::STATUS_DRAFT;
            $test->title = "IELTS " . ucfirst($type) . " Test - " . date('Y-m-d H:i');
            $test->description = "AI Generated test" . ($topic ? " on topic: {$topic}" : '');
            $test->duration = $type === Test::TYPE_LISTENING ? 30 : 60;
            $test->created_by = $userId;

            if (!$test->save()) {
                throw new \Exception("Failed to create test: " . Json::encode($test->errors));
            }

            if ($type === Test::TYPE_LISTENING) {
                $this->generateListeningTest($test, $difficulty, $topic);
            } else {
                $this->generateReadingTest($test, $difficulty, $topic);
            }

            // Update total questions count
            $test->updateQuestionsCount();

            // Mark test as published
            $test->status = Test::STATUS_PUBLISHED;
            $test->save(false);

            $transaction->commit();

            return [
                'success' => true,
                'test_id' => $test->id,
                'message' => 'Test successfully generated'
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Test generation failed: " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate Listening Test
     */
    private function generateListeningTest($test, $difficulty, $topic)
    {
        // 4 ta section yaratamiz
        for ($sectionNum = 1; $sectionNum <= 4; $sectionNum++) {
            // AI dan script olish
            $script = $this->generateListeningScript($sectionNum, $difficulty, $topic);

            // Audio generatsiya qilish
            $audioUrl = $this->generateAudio($script['text'], $script['speakers']);

            // Section yaratish
            $section = new ListeningSection();
            $section->test_id = $test->id;
            $section->section_number = $sectionNum;
            $section->title = $script['title'] ?? "Section {$sectionNum}";
            $section->audio_url = $audioUrl;
            $section->audio_duration = $this->calculateAudioDuration($script['text']);
            $section->context = $script['context'];
            $section->transcript = $script['text'];

            if (!$section->save()) {
                throw new \Exception("Failed to save section: " . Json::encode($section->errors));
            }

            // Savollar generatsiya qilish
            $this->generateListeningQuestions($test, $section, $script, $difficulty);
        }
    }

    /**
     * Generate listening script using AI
     */
    private function generateListeningScript($sectionNum, $difficulty, $topic)
    {
        $contextMap = [
            1 => 'A conversation between two people in a social/everyday context (e.g., booking a hotel, arranging a meeting, shopping at a store)',
            2 => 'A monologue in a social context (e.g., a speech about local facilities, a radio broadcast, a tour guide presentation)',
            3 => 'A conversation between multiple people in an educational/training context (e.g., university tutors and students discussing an assignment)',
            4 => 'A monologue on an academic subject (e.g., a university lecture on biology, history, or engineering)'
        ];

        $prompt = "Generate an IELTS Listening Section {$sectionNum} script.

Context: {$contextMap[$sectionNum]}
Difficulty: {$difficulty}
Topic: " . ($topic ?: 'General appropriate topic for this section') . "

Requirements:
1. The script should be approximately 600-800 words
2. For conversations, include natural dialogue between speakers
3. Include natural pauses, fillers (um, well, you know), and realistic conversational flow
4. Use appropriate vocabulary for {$difficulty} level:
   - Easy: Common everyday words, simple sentence structures
   - Medium: Mix of common and academic vocabulary, varied sentences
   - Hard: Advanced vocabulary, complex structures, idioms
5. Include specific information that can be tested:
   - Names and spellings
   - Numbers, dates, times, prices
   - Locations and addresses
   - Opinions and reasons
   - Specific details and facts
6. For Section 1-2: Use casual, everyday language
7. For Section 3-4: Use more academic and formal language

IMPORTANT: Return ONLY valid JSON, no other text.

Return in this EXACT JSON format:
{
    \"title\": \"Brief title for the section\",
    \"context\": \"Brief description of the scenario\",
    \"text\": \"Full dialogue or monologue with speaker names like 'Speaker1: ...' for conversations\",
    \"speakers\": [
        {\"name\": \"Speaker1\", \"voice\": \"male\"},
        {\"name\": \"Speaker2\", \"voice\": \"female\"}
    ],
    \"key_information\": [
        {\"type\": \"name\", \"value\": \"John Smith\"},
        {\"type\": \"date\", \"value\": \"15th March\"},
        {\"type\": \"number\", \"value\": \"250\"},
        {\"type\": \"location\", \"value\": \"Central Library\"}
    ]
}";

        $response = $this->callOpenAI($prompt);
        return Json::decode($response);
    }

    /**
     * Generate listening questions
     */
    private function generateListeningQuestions($test, $section, $script, $difficulty)
    {
        $prompt = "Based on this IELTS listening script, generate 10 questions.

Script:
{$script['text']}

Key Information Available:
" . Json::encode($script['key_information']) . "

Generate questions using these types (must include all):
1. Multiple Choice - 3 questions (4 options each: A, B, C, D)
2. Form/Note Completion - 3 questions (fill in blanks)
3. Matching - 2 questions (match items to categories)
4. Short Answer - 2 questions (one or two word answers)

Difficulty Level: {$difficulty}

Requirements:
- Questions must be answerable from the script
- For form completion: specify word limit (e.g., NO MORE THAN TWO WORDS)
- Answers should be exact as heard in the recording
- Include spelling variations where applicable
- Questions should appear in the order information appears in the script
- For multiple choice, make distractors plausible but clearly incorrect

IMPORTANT: Return ONLY valid JSON, no other text or markdown.

Return in this EXACT JSON format:
[
    {
        \"type\": \"multiple_choice\",
        \"question_number\": 1,
        \"question_text\": \"What is the main purpose of the call?\",
        \"options\": [\"A) To make a booking\", \"B) To cancel a reservation\", \"C) To ask for information\", \"D) To make a complaint\"],
        \"correct_answer\": \"A\",
        \"explanation\": \"The speaker says 'I'd like to book a room' at the beginning\"
    },
    {
        \"type\": \"form_completion\",
        \"question_number\": 4,
        \"instruction\": \"Complete the form below. Write NO MORE THAN TWO WORDS AND/OR A NUMBER for each answer.\",
        \"fields\": [
            {
                \"field_number\": 4,
                \"prompt\": \"Name:\",
                \"correct_answer\": \"John Smith\",
                \"acceptable_answers\": [\"John Smith\", \"john smith\", \"JOHN SMITH\"]
            }
        ]
    },
    {
        \"type\": \"matching\",
        \"question_number\": 7,
        \"instruction\": \"Match each feature with the correct room type.\",
        \"items\": [
            {
                \"item_number\": 7,
                \"left_side\": \"Sea view\",
                \"options\": [\"A) Single Room\", \"B) Double Room\", \"C) Suite\"],
                \"correct_answer\": \"C\"
            }
        ]
    },
    {
        \"type\": \"short_answer\",
        \"question_number\": 9,
        \"question_text\": \"What time does the museum close? Write NO MORE THAN TWO WORDS.\",
        \"correct_answer\": \"five thirty\",
        \"acceptable_answers\": [\"five thirty\", \"5:30\", \"5.30\", \"half past five\"]
    }
]";

        $questionsData = Json::decode($this->callOpenAI($prompt));

        foreach ($questionsData as $index => $qData) {
            $question = new Question();
            $question->test_id = $test->id;
            $question->section_id = $section->id;
            $question->question_number = ($section->section_number - 1) * 10 + $index + 1;
            $question->type = $qData['type'];
            $question->question_text = $qData['question_text'] ?? '';
            $question->instruction = $qData['instruction'] ?? '';
            $question->explanation = $qData['explanation'] ?? '';
            $question->order = $index + 1;

            // Extract correct answer
            $correctAnswer = $this->extractCorrectAnswer($qData);

            // Remove correct answer from question data
            $questionData = $qData;
            unset($questionData['correct_answer']);
            unset($questionData['explanation']);

            $question->setQuestionData($questionData);
            $question->setCorrectAnswerData($correctAnswer);

            if (!$question->save()) {
                throw new \Exception("Failed to save question: " . Json::encode($question->errors));
            }
        }
    }

    /**
     * Generate Reading Test
     */
    private function generateReadingTest($test, $difficulty, $topic)
    {
        // 3 ta passage yaratamiz
        for ($passageNum = 1; $passageNum <= 3; $passageNum++) {
            $passageData = $this->generateReadingPassage($passageNum, $difficulty, $topic);

            // Passage yaratish
            $passage = new ReadingPassage();
            $passage->test_id = $test->id;
            $passage->passage_number = $passageNum;
            $passage->title = $passageData['title'];
            $passage->text = $passageData['text'];
            $passage->word_count = str_word_count(strip_tags($passageData['text']));

            if (!$passage->save()) {
                throw new \Exception("Failed to save passage: " . Json::encode($passage->errors));
            }

            // Savollar generatsiya qilish
            $this->generateReadingQuestions($test, $passage, $passageData, $difficulty);
        }
    }

    /**
     * Generate reading passage
     */
    private function generateReadingPassage($passageNum, $difficulty, $topic)
    {
        $difficultyMap = [
            1 => ['easy', 850, 'general interest'],
            2 => ['medium', 900, 'work-related or academic'],
            3 => ['hard', 950, 'complex academic']
        ];

        $passDifficulty = $difficultyMap[$passageNum][0];
        $wordCount = $difficultyMap[$passageNum][1];
        $style = $difficultyMap[$passageNum][2];

        $prompt = "Generate an IELTS Reading Passage {$passageNum}.

Difficulty: {$passDifficulty}
Style: {$style}
Topic: " . ($topic ?: 'Choose an interesting and appropriate academic topic') . "
Word Count: Approximately {$wordCount} words

Requirements:
1. Write in an academic/formal style appropriate for IELTS
2. Include 5-7 well-structured paragraphs
3. Each paragraph should have a clear main idea
4. Include:
   - Facts and statistics
   - Different viewpoints or arguments
   - Cause and effect relationships
   - Comparisons and contrasts
   - Definitions and explanations
5. Use vocabulary appropriate for {$passDifficulty} level
6. Include information suitable for various question types:
   - Statements that can be True/False/Not Given
   - Paragraphs that can have headings
   - Sentences that can be completed
   - Information that can be matched
7. Make some information explicit and some implicit
8. Include some information that might seem relevant but is actually Not Given

IMPORTANT: Return ONLY valid JSON, no other text.

Return in this EXACT JSON format:
{
    \"title\": \"Engaging title for the passage\",
    \"text\": \"Full passage text. Use \\n\\n to separate paragraphs. Label paragraphs as [A], [B], [C] etc. at the start of each paragraph.\",
    \"paragraphs\": [
        {
            \"id\": \"A\",
            \"main_idea\": \"Brief description of paragraph's main idea\",
            \"suggested_heading\": \"Possible heading for this paragraph\"
        }
    ],
    \"key_points\": [
        \"Important fact 1 that is explicitly stated\",
        \"Important fact 2 that is implied\",
        \"Something that might be assumed but is NOT mentioned\"
    ]
}";

        $response = $this->callOpenAI($prompt, 'gpt-4o');
        return Json::decode($response);
    }

    /**
     * Generate reading questions
     */
    private function generateReadingQuestions($test, $passage, $passageData, $difficulty)
    {
        $prompt = "Based on this IELTS reading passage, generate 13-14 questions using various question types.

Passage Title: {$passageData['title']}
Passage Text:
{$passageData['text']}

Paragraph Information:
" . Json::encode($passageData['paragraphs']) . "

Generate questions in these EXACT types and quantities:
1. True/False/Not Given - 4 questions
2. Multiple Choice - 3 questions (with 4 options each)
3. Matching Headings - 3 questions
4. Sentence Completion - 3 questions (using words from passage)
5. Short Answer - 1 question

Requirements:
- True/False/Not Given: Mix of all three answer types. 'Not Given' means information is not in the passage at all
- Multiple Choice: One clearly correct answer, three plausible distractors
- Matching Headings: Provide heading options i, ii, iii, iv, v, vi (more options than paragraphs)
- Sentence Completion: Specify word limit and answers must be exact words from passage
- All questions must be answerable from the passage
- Questions should test different skills: detail, main idea, inference, vocabulary

IMPORTANT: Return ONLY valid JSON, no other text.

Return in this EXACT JSON format:
[
    {
        \"type\": \"true_false_notgiven\",
        \"question_number\": 1,
        \"statement\": \"The research was conducted over a five-year period.\",
        \"correct_answer\": \"TRUE\",
        \"explanation\": \"Paragraph B states 'the five-year study'\"
    },
    {
        \"type\": \"multiple_choice\",
        \"question_number\": 5,
        \"question_text\": \"According to the passage, what was the main cause of the decline?\",
        \"options\": [
            \"A) Climate change\",
            \"B) Human activity\",
            \"C) Natural disasters\",
            \"D) Disease\"
        ],
        \"correct_answer\": \"B\",
        \"explanation\": \"Paragraph C explicitly mentions human activity as the primary factor\"
    },
    {
        \"type\": \"matching_headings\",
        \"question_number\": 8,
        \"instruction\": \"Choose the correct heading for each paragraph from the list of headings below.\",
        \"heading_options\": [
            \"i) Early developments in the field\",
            \"ii) Challenges facing researchers\",
            \"iii) Future implications\",
            \"iv) Unexpected discoveries\",
            \"v) Methodology and approach\",
            \"vi) Conflicting viewpoints\"
        ],
        \"paragraphs\": [
            {
                \"paragraph_id\": \"A\",
                \"correct_answer\": \"i\"
            },
            {
                \"paragraph_id\": \"C\",
                \"correct_answer\": \"iv\"
            }
        ]
    },
    {
        \"type\": \"sentence_completion\",
        \"question_number\": 11,
        \"instruction\": \"Complete the sentences below. Choose NO MORE THAN TWO WORDS from the passage for each answer.\",
        \"sentence\": \"The study revealed that ___ was the most significant factor.\",
        \"correct_answer\": \"human activity\",
        \"acceptable_answers\": [\"human activity\", \"Human activity\", \"HUMAN ACTIVITY\"]
    },
    {
        \"type\": \"short_answer\",
        \"question_number\": 14,
        \"instruction\": \"Answer the question below. Choose NO MORE THAN THREE WORDS from the passage.\",
        \"question_text\": \"What did the researchers use to collect their data?\",
        \"correct_answer\": \"satellite images\",
        \"acceptable_answers\": [\"satellite images\", \"Satellite images\", \"satellite imagery\"]
    }
]";

        $questionsData = Json::decode($this->callOpenAI($prompt, 'gpt-4o'));

        foreach ($questionsData as $index => $qData) {
            $question = new Question();
            $question->test_id = $test->id;
            $question->passage_id = $passage->id;
            $question->question_number = ($passage->passage_number - 1) * 14 + $index + 1;
            $question->type = $qData['type'];
            $question->question_text = $qData['statement'] ?? $qData['question_text'] ?? $qData['sentence'] ?? '';
            $question->instruction = $qData['instruction'] ?? '';
            $question->explanation = $qData['explanation'] ?? '';
            $question->order = $index + 1;

            $correctAnswer = $this->extractCorrectAnswer($qData);

            $questionData = $qData;
            unset($questionData['correct_answer']);
            unset($questionData['explanation']);

            $question->setQuestionData($questionData);
            $question->setCorrectAnswerData($correctAnswer);

            if (!$question->save()) {
                throw new \Exception("Failed to save question: " . Json::encode($question->errors));
            }
        }
    }

    /**
     * Generate audio using Text-to-Speech
     */
    private function generateAudio($text, $speakers)
    {
        try {
            // Simple version: generate single audio file
            // In production, you would split by speaker and merge

            $voiceId = isset($speakers[0]) && $speakers[0]['voice'] === 'male'
                ? 'onwK4e9ZLuTAKqWW03F9' // Default male voice
                : 'EXAVITQu4vr4xnSDxMaL'; // Default female voice

            $audioUrl = $this->textToSpeech($text, $voiceId);

            return $audioUrl;

        } catch (\Exception $e) {
            Yii::error("Audio generation failed: " . $e->getMessage());
            // Return a placeholder or null
            return null;
        }
    }

    /**
     * Text to speech using ElevenLabs or OpenAI
     */
    private function textToSpeech($text, $voiceId = null)
    {
        // Option 1: ElevenLabs (better quality)
        if (isset(Yii::$app->params['elevenLabsApiKey'])) {
            return $this->elevenLabsTextToSpeech($text, $voiceId);
        }

        // Option 2: OpenAI TTS (fallback)
        return $this->openAiTextToSpeech($text);
    }

    /**
     * ElevenLabs Text to Speech
     */
    private function elevenLabsTextToSpeech($text, $voiceId)
    {
        $voiceId = $voiceId ?: 'EXAVITQu4vr4xnSDxMaL';

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'xi-api-key: ' . Yii::$app->params['elevenLabsApiKey'],
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'text' => $text,
                'model_id' => 'eleven_multilingual_v2',
                'voice_settings' => [
                    'stability' => 0.5,
                    'similarity_boost' => 0.75,
                    'style' => 0.0,
                    'use_speaker_boost' => true
                ]
            ])
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200) {
            throw new \Exception("ElevenLabs API error: HTTP {$httpCode}");
        }

        // Save audio file
        $filename = 'audio_' . uniqid() . '.mp3';
        $uploadPath = Yii::getAlias('@frontend/web/uploads/audio/');

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        file_put_contents($uploadPath . $filename, $response);

        return '/uploads/audio/' . $filename;
    }

    /**
     * OpenAI Text to Speech (fallback)
     */
    private function openAiTextToSpeech($text)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.openai.com/v1/audio/speech",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'tts-1',
                'input' => $text,
                'voice' => 'alloy',
                'speed' => 1.0
            ])
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        // Save audio file
        $filename = 'audio_' . uniqid() . '.mp3';
        $uploadPath = Yii::getAlias('@frontend/web/uploads/audio/');

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        file_put_contents($uploadPath . $filename, $response);

        return '/uploads/audio/' . $filename;
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI($prompt, $model = 'gpt-4o-mini')
    {
        $curl = curl_init();

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert IELTS test creator with years of experience creating authentic, high-quality IELTS test materials. You understand all IELTS question types, difficulty levels, and marking criteria. Always return valid JSON responses only.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->openAiEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7,
                'response_format' => ['type' => 'json_object']
            ])
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200) {
            $error = Json::decode($response);
            throw new \Exception("OpenAI API Error: " . ($error['error']['message'] ?? 'Unknown error'));
        }

        $result = Json::decode($response);

        if (!isset($result['choices'][0]['message']['content'])) {
            throw new \Exception("Invalid OpenAI response format");
        }

        return $result['choices'][0]['message']['content'];
    }

    /**
     * Extract correct answer from question data
     */
    private function extractCorrectAnswer($qData)
    {
        if (isset($qData['correct_answer'])) {
            return $qData['correct_answer'];
        }

        if (isset($qData['fields'])) {
            return [
                'fields' => array_map(function($field) {
                    return [
                        'correct_answer' => $field['correct_answer'],
                        'acceptable_answers' => $field['acceptable_answers'] ?? [$field['correct_answer']]
                    ];
                }, $qData['fields'])
            ];
        }

        if (isset($qData['items'])) {
            return [
                'matches' => array_map(function($item) {
                    return $item['correct_answer'];
                }, $qData['items'])
            ];
        }

        if (isset($qData['paragraphs'])) {
            return [
                'matches' => array_reduce($qData['paragraphs'], function($carry, $para) {
                    $carry[$para['paragraph_id']] = $para['correct_answer'];
                    return $carry;
                }, [])
            ];
        }

        if (isset($qData['acceptable_answers'])) {
            return [
                'answer' => $qData['acceptable_answers'][0],
                'acceptable_answers' => $qData['acceptable_answers']
            ];
        }

        return null;
    }

    /**
     * Calculate audio duration (approximate)
     */
    private function calculateAudioDuration($text)
    {
        // Average speaking speed: 150 words per minute
        $wordCount = str_word_count($text);
        return ceil(($wordCount / 150) * 60); // in seconds
    }
}
