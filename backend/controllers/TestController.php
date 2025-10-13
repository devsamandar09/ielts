<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use common\models\Test;
use common\models\TestAttempt;
use common\services\AiTestGenerator;

class TestController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'generate' => ['POST'],
                    'publish' => ['POST'],
                    'archive' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Test models
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Test::find()->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Test model
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Get statistics
        $stats = [
            'total_attempts' => $model->getTotalAttempts(),
            'completed_attempts' => $model->getCompletedAttempts(),
            'average_score' => round($model->getAverageScore(), 2),
        ];

        return $this->render('view', [
            'model' => $model,
            'stats' => $stats,
        ]);
    }

    /**
     * Create new test page
     */
    public function actionCreate()
    {
        return $this->render('create');
    }

    /**
     * Generate test using AI
     */
    public function actionGenerate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $type = Yii::$app->request->post('type');
        $difficulty = Yii::$app->request->post('difficulty', 'medium');
        $topic = Yii::$app->request->post('topic');

        if (!in_array($type, ['listening', 'reading'])) {
            return [
                'success' => false,
                'message' => 'Invalid test type. Must be "listening" or "reading".'
            ];
        }

        if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
            return [
                'success' => false,
                'message' => 'Invalid difficulty level.'
            ];
        }

        // ENV dan provayder va kalitlarni olish
        $provider = getenv('PROVIDER');
        $claudeApiKey = getenv('CLAUDE_API_KEY');

        if ($provider !== 'claude' || empty($claudeApiKey)) {
            return [
                'success' => false,
                'message' => 'Claude API key topilmadi yoki noto‘g‘ri sozlangan.'
            ];
        }

        try {
            // Claude API chaqiruv
            $prompt = "Generate a {$difficulty} level {$type} test on the topic: {$topic}. 
                       Include 5 questions with multiple-choice answers and mark the correct one.";

            $response = $this->callClaudeAPI($prompt, $claudeApiKey);

            return [
                'success' => true,
                'provider' => $provider,
                'data' => $response,
            ];

        } catch (\Exception $e) {
            Yii::error("Claude test generation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error generating test: ' . $e->getMessage()
            ];
        }
    }
    private function callClaudeAPI($prompt, $apiKey)
    {
        $ch = curl_init('https://api.anthropic.com/v1/messages');
        $data = [
            "model" => "claude-3-sonnet-20240229",
            "max_tokens" => 1000,
            "messages" => [
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ]
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "x-api-key: $apiKey",
                "anthropic-version: 2023-06-01",
                "content-type: application/json"
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception("CURL error: " . curl_error($ch));
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode !== 200) {
            throw new \Exception("Claude API returned status $statusCode: $response");
        }

        $json = json_decode($response, true);
        return $json['content'][0]['text'] ?? 'No response content';
    }

    /**
     * Publish test
     */
    public function actionPublish($id)
    {
        $model = $this->findModel($id);

        if ($model->publish()) {
            Yii::$app->session->setFlash('success', 'Test published successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to publish test.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Archive test
     */
    public function actionArchive($id)
    {
        $model = $this->findModel($id);

        if ($model->archive()) {
            Yii::$app->session->setFlash('success', 'Test archived successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to archive test.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Delete test
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Test deleted successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete test.');
        }

        return $this->redirect(['index']);
    }

    /**
     * View test statistics
     */
    public function actionStatistics($id)
    {
        $model = $this->findModel($id);

        $attemptsProvider = new ActiveDataProvider([
            'query' => TestAttempt::find()
                ->where(['test_id' => $id])
                ->with(['user'])
                ->orderBy(['completed_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        // Calculate detailed statistics
        $attempts = TestAttempt::find()
            ->where(['test_id' => $id, 'status' => TestAttempt::STATUS_COMPLETED])
            ->all();

        $bandScoreDistribution = [];
        foreach ($attempts as $attempt) {
            $band = (string) $attempt->band_score;
            if (!isset($bandScoreDistribution[$band])) {
                $bandScoreDistribution[$band] = 0;
            }
            $bandScoreDistribution[$band]++;
        }

        ksort($bandScoreDistribution);

        return $this->render('statistics', [
            'model' => $model,
            'attemptsProvider' => $attemptsProvider,
            'bandScoreDistribution' => $bandScoreDistribution,
        ]);
    }

    /**
     * Finds the Test model based on its primary key value
     */
    protected function findModel($id)
    {
        if (($model = Test::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested test does not exist.');
    }
}
