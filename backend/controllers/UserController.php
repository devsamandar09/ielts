<?php

namespace backend\controllers;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use common\models\User;
use common\models\TestAttempt;
use common\models\UserProgress;

class UserController extends Controller
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
                    'block' => ['POST'],
                    'unblock' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()
                ->where(['role' => User::ROLE_USER])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Get user's test attempts
        $attemptsProvider = new ActiveDataProvider([
            'query' => TestAttempt::find()
                ->where(['user_id' => $id])
                ->with(['test'])
                ->orderBy(['started_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        // Get user progress
        $listeningProgress = UserProgress::getProgress($id, Test::TYPE_LISTENING);
        $readingProgress = UserProgress::getProgress($id, Test::TYPE_READING);

        return $this->render('view', [
            'model' => $model,
            'attemptsProvider' => $attemptsProvider,
            'listeningProgress' => $listeningProgress,
            'readingProgress' => $readingProgress,
        ]);
    }

    /**
     * Block user
     */
    public function actionBlock($id)
    {
        $model = $this->findModel($id);
        $model->status = User::STATUS_INACTIVE;

        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'User blocked successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to block user.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Unblock user
     */
    public function actionUnblock($id)
    {
        $model = $this->findModel($id);
        $model->status = User::STATUS_ACTIVE;

        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'User unblocked successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to unblock user.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Delete user
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'User deleted successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete user.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested user does not exist.');
    }
}
