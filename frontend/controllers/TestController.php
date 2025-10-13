<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use common\models\Test;
use common\models\TestAttempt;
use common\models\UserAnswer;
use common\models\Question;

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
                    'start' => ['post'],
                    'save-answer' => ['post'],
                    'submit' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists available tests
     */
    public function actionIndex($type = null)
    {
        $query = Test::findPublished();

        if ($type && in_array($type, [Test::TYPE_LISTENING, Test::TYPE_READING])) {
            $query->andWhere(['type' => $type]);
        }

        $tests = $query->orderBy(['created_at' => SORT_DESC])->all();

        return $this->render('index', [
            'tests' => $tests,
            'type' => $type,
        ]);
    }

    /**
     * Display test details
     */
    public function actionView($id)
    {
        $test = $this->findTest($id);

        // Check if user has an in-progress attempt
        $inProgressAttempt = TestAttempt::find()
            ->where([
                'user_id' => Yii::$app->user->id,
                'test_id' => $test->id,
                'status' => TestAttempt::STATUS_IN_PROGRESS
            ])
            ->one();

        // Get user's previous attempts
        $previousAttempts = TestAttempt::find()
            ->where([
                'user_id' => Yii::$app->user->id,
                'test_id' => $test->id,
                'status' => TestAttempt::STATUS_COMPLETED
            ])
            ->orderBy(['completed_at' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('view', [
            'test' => $test,
            'inProgressAttempt' => $inProgressAttempt,
            'previousAttempts' => $previousAttempts,
        ]);
    }

    /**
     * Start a test
     */
    public function actionStart($id)
    {
        $test = $this->findTest($id);

        $attempt = TestAttempt::startTest(Yii::$app->user->id, $test->id);

        if (!$attempt) {
            Yii::$app->session->setFlash('error', 'Failed to start test.');
            return $this->redirect(['view', 'id' => $test->id]);
        }

        return $this->redirect(['take', 'id' => $attempt->id]);
    }

    /**
     * Take test (main interface)
     */
    public function actionTake($id)
    {
        $attempt = $this->findAttempt($id);

        // Check if attempt belongs to current user
        if ($attempt->user_id != Yii::$app->user->id) {
            throw new NotFoundHttpException('Test attempt not found.');
        }

        // Check if already completed
        if ($attempt->isCompleted()) {
            return $this->redirect(['result', 'id' => $attempt->id]);
        }

        $test = $attempt->test;

        // Load user's current answers
        $userAnswers = [];
        foreach ($attempt->userAnswers as $answer) {
            $userAnswers[$answer->question_id] = $answer->getUserAnswerData();
        }

        return $this->render('take', [
            'test' => $test,
            'attempt' => $attempt,
            'userAnswers' => $userAnswers,
        ]);
    }

    /**
     * Save answer (AJAX)
     */
    public function actionSaveAnswer()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $attemptId = Yii::$app->request->post('attempt_id');
        $questionId = Yii::$app->request->post('question_id');
        $answer = Yii::$app->request->post('answer');

        $attempt = $this->findAttempt($attemptId);

        // Verify ownership
        if ($attempt->user_id != Yii::$app->user->id) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        // Verify test is in progress
        if (!$attempt->isInProgress()) {
            return ['success' => false, 'message' => 'Test is not in progress'];
        }

        // Save answer
        $success = UserAnswer::saveAnswer($attemptId, $questionId, $answer);

        return ['success' => $success];
    }

    /**
     * Submit test
     */
    public function actionSubmit($id)
    {
        $attempt = $this->findAttempt($id);

        // Check ownership
        if ($attempt->user_id != Yii::$app->user->id) {
            throw new NotFoundHttpException('Test attempt not found.');
        }

        // Complete the test
        $attempt->completeTest();

        Yii::$app->session->setFlash('success', 'Test submitted successfully!');

        return $this->redirect(['result', 'id' => $attempt->id]);
    }

    /**
     * Display test results
     */
    public function actionResult($id)
    {
        $attempt = $this->findAttempt($id);

        // Check ownership
        if ($attempt->user_id != Yii::$app->user->id) {
            throw new NotFoundHttpException('Test attempt not found.');
        }

        // Check if completed
        if (!$attempt->isCompleted()) {
            return $this->redirect(['take', 'id' => $attempt->id]);
        }

        $test = $attempt->test;

        // Get all questions with user answers
        $questionsWithAnswers = [];
        foreach ($test->questions as $question) {
            $userAnswer = UserAnswer::findOne([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ]);

            $questionsWithAnswers[] = [
                'question' => $question,
                'userAnswer' => $userAnswer,
            ];
        }

        return $this->render('result', [
            'attempt' => $attempt,
            'test' => $test,
            'questionsWithAnswers' => $questionsWithAnswers,
        ]);
    }

    /**
     * Find test model
     */
    protected function findTest($id)
    {
        $test = Test::findOne(['id' => $id, 'status' => Test::STATUS_PUBLISHED]);

        if ($test === null) {
            throw new NotFoundHttpException('The requested test is not available.');
        }

        return $test;
    }

    /**
     * Find attempt model
     */
    protected function findAttempt($id)
    {
        $attempt = TestAttempt::findOne($id);

        if ($attempt === null) {
            throw new NotFoundHttpException('Test attempt not found.');
        }

        return $attempt;
    }
}



