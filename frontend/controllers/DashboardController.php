<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\Test;
use common\models\TestAttempt;
use common\models\UserProgress;

class DashboardController extends Controller
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
        ];
    }

    /**
     * Displays user dashboard
     */
    public function actionIndex()
    {
        $userId = Yii::$app->user->id;

        // Get user progress
        $listeningProgress = UserProgress::getProgress($userId, Test::TYPE_LISTENING);
        $readingProgress = UserProgress::getProgress($userId, Test::TYPE_READING);

        // Get recent attempts
        $recentAttempts = TestAttempt::find()
            ->where(['user_id' => $userId])
            ->with(['test'])
            ->orderBy(['started_at' => SORT_DESC])
            ->limit(5)
            ->all();

        // Get available tests
        $availableListeningTests = Test::findPublished()
            ->where(['type' => Test::TYPE_LISTENING])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(6)
            ->all();

        $availableReadingTests = Test::findPublished()
            ->where(['type' => Test::TYPE_READING])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(6)
            ->all();

        // Overall statistics
        $totalAttempts = TestAttempt::find()
            ->where(['user_id' => $userId, 'status' => TestAttempt::STATUS_COMPLETED])
            ->count();

        $averageBandScore = TestAttempt::find()
            ->where(['user_id' => $userId, 'status' => TestAttempt::STATUS_COMPLETED])
            ->average('band_score');

        return $this->render('index', [
            'listeningProgress' => $listeningProgress,
            'readingProgress' => $readingProgress,
            'recentAttempts' => $recentAttempts,
            'availableListeningTests' => $availableListeningTests,
            'availableReadingTests' => $availableReadingTests,
            'totalAttempts' => $totalAttempts,
            'averageBandScore' => round($averageBandScore, 1),
        ]);
    }

    /**
     * Display user's test history
     */
    public function actionHistory()
    {
        $userId = Yii::$app->user->id;

        $attempts = TestAttempt::find()
            ->where(['user_id' => $userId])
            ->with(['test'])
            ->orderBy(['started_at' => SORT_DESC])
            ->all();

        return $this->render('history', [
            'attempts' => $attempts,
        ]);
    }

    /**
     * Display user's progress statistics
     */
    public function actionProgress()
    {
        $userId = Yii::$app->user->id;

        // Get all completed attempts
        $attempts = TestAttempt::find()
            ->where(['user_id' => $userId, 'status' => TestAttempt::STATUS_COMPLETED])
            ->with(['test'])
            ->orderBy(['completed_at' => SORT_ASC])
            ->all();

        // Prepare chart data
        $listeningData = [];
        $readingData = [];

        foreach ($attempts as $attempt) {
            $date = date('Y-m-d', $attempt->completed_at);
            $score = $attempt->band_score;

            if ($attempt->test->type === Test::TYPE_LISTENING) {
                $listeningData[] = [
                    'date' => $date,
                    'score' => $score,
                ];
            } else {
                $readingData[] = [
                    'date' => $date,
                    'score' => $score,
                ];
            }
        }

        // Get progress by question type (future enhancement)

        return $this->render('progress', [
            'listeningData' => $listeningData,
            'readingData' => $readingData,
            'attempts' => $attempts,
        ]);
    }
}


