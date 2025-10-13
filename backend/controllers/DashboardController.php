<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\Test;
use common\models\TestAttempt;
use common\models\User;

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
     * Displays dashboard
     */
    public function actionIndex()
    {
        // Overall statistics
        $stats = [
            'total_tests' => Test::find()->count(),
            'published_tests' => Test::find()->where(['status' => Test::STATUS_PUBLISHED])->count(),
            'total_users' => User::find()->where(['role' => User::ROLE_USER])->count(),
            'total_attempts' => TestAttempt::find()->count(),
            'completed_attempts' => TestAttempt::find()->where(['status' => TestAttempt::STATUS_COMPLETED])->count(),
        ];

        // Recent tests
        $recentTests = Test::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        // Recent attempts
        $recentAttempts = TestAttempt::find()
            ->with(['user', 'test'])
            ->orderBy(['started_at' => SORT_DESC])
            ->limit(10)
            ->all();

        // Test type distribution
        $listeningTests = Test::find()->where(['type' => Test::TYPE_LISTENING])->count();
        $readingTests = Test::find()->where(['type' => Test::TYPE_READING])->count();

        // Average scores by test type
        $avgListeningScore = TestAttempt::find()
            ->joinWith('test')
            ->where(['test.type' => Test::TYPE_LISTENING, 'test_attempt.status' => TestAttempt::STATUS_COMPLETED])
            ->average('test_attempt.band_score');

        $avgReadingScore = TestAttempt::find()
            ->joinWith('test')
            ->where(['test.type' => Test::TYPE_READING, 'test_attempt.status' => TestAttempt::STATUS_COMPLETED])
            ->average('test_attempt.band_score');

        // Monthly attempts chart data (last 6 months)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $startTime = strtotime("-{$i} months", strtotime('first day of this month'));
            $endTime = strtotime("+1 month", $startTime);

            $count = TestAttempt::find()
                ->where(['>=', 'started_at', $startTime])
                ->andWhere(['<', 'started_at', $endTime])
                ->count();

            $monthlyData[] = [
                'month' => date('M Y', $startTime),
                'count' => $count,
            ];
        }

        return $this->render('index', [
            'stats' => $stats,
            'recentTests' => $recentTests,
            'recentAttempts' => $recentAttempts,
            'listeningTests' => $listeningTests,
            'readingTests' => $readingTests,
            'avgListeningScore' => round($avgListeningScore, 1),
            'avgReadingScore' => round($avgReadingScore, 1),
            'monthlyData' => $monthlyData,
        ]);
    }
}
