<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="dashboard-index">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body p-4">
                    <h2><i class="fas fa-hand-wave"></i> Welcome back, <?= Html::encode(Yii::$app->user->identity->getFullName()) ?>!</h2>
                    <p class="lead mb-0">Let's continue your IELTS preparation journey</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card stats-card blue">
                <div class="card-body text-center">
                    <i class="fas fa-clipboard-check fa-3x text-primary mb-2"></i>
                    <h3 class="mb-0"><?= $totalAttempts ?></h3>
                    <p class="text-muted mb-0">Tests Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card green">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-3x text-success mb-2"></i>
                    <h3 class="mb-0"><?= $averageBandScore ?: 'N/A' ?></h3>
                    <p class="text-muted mb-0">Average Band Score</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card orange">
                <div class="card-body text-center">
                    <i class="fas fa-headphones fa-3x text-warning mb-2"></i>
                    <h3 class="mb-0"><?= $listeningProgress ? $listeningProgress->total_tests_taken : 0 ?></h3>
                    <p class="text-muted mb-0">Listening Tests</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card purple">
                <div class="card-body text-center">
                    <i class="fas fa-book-open fa-3x text-info mb-2"></i>
                    <h3 class="mb-0"><?= $readingProgress ? $readingProgress->total_tests_taken : 0 ?></h3>
                    <p class="text-muted mb-0">Reading Tests</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Overview -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-headphones"></i> Listening Progress</h5>
                </div>
                <div class="card-body">
                    <?php if ($listeningProgress): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="progress-circle" style="--progress: <?= ($listeningProgress->average_band_score / 9) * 360 ?>deg">
                                    <span class="progress-value"><?= $listeningProgress->average_band_score ?></span>
                                </div>
                                <p class="text-center mt-2"><strong>Average Band Score</strong></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Tests:</strong> <?= $listeningProgress->total_tests_taken ?></p>
                                <p><strong>Best Score:</strong> <?= $listeningProgress->best_band_score ?></p>
                                <p><strong>Time Spent:</strong> <?= $listeningProgress->getTotalTimeSpentFormatted() ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p>No listening tests taken yet</p>
                            <a href="<?= Url::to(['/test/index', 'type' => 'listening']) ?>" class="btn btn-primary">
                                Start First Test
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-book-open"></i> Reading Progress</h5>
                </div>
                <div class="card-body">
                    <?php if ($readingProgress): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="progress-circle" style="--progress: <?= ($readingProgress->average_band_score / 9) * 360 ?>deg">
                                    <span class="progress-value"><?= $readingProgress->average_band_score ?></span>
                                </div>
                                <p class="text-center mt-2"><strong>Average Band Score</strong></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Tests:</strong> <?= $readingProgress->total_tests_taken ?></p>
                                <p><strong>Best Score:</strong> <?= $readingProgress->best_band_score ?></p>
                                <p><strong>Time Spent:</strong> <?= $readingProgress->getTotalTimeSpentFormatted() ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p>No reading tests taken yet</p>
                            <a href="<?= Url::to(['/test/index', 'type' => 'reading']) ?>" class="btn btn-primary">
                                Start First Test
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attempts -->
    <?php if (!empty($recentAttempts)): ?>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-history"></i> Recent Tests</h5>
                        <a href="<?= Url::to(['/dashboard/history']) ?>" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Test</th>
                                    <th>Type</th>
                                    <th>Score</th>
                                    <th>Band</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($recentAttempts as $attempt): ?>
                                    <tr>
                                        <td><?= Html::encode($attempt->test->title) ?></td>
                                        <td>
                                        <span class="badge badge-<?= $attempt->test->type === 'listening' ? 'primary' : 'info' ?>">
                                            <?= ucfirst($attempt->test->type) ?>
                                        </span>
                                        </td>
                                        <td>
                                            <?php if ($attempt->isCompleted()): ?>
                                                <?= $attempt->total_correct ?>/<?= $attempt->total_questions ?>
                                            <?php else: ?>
                                                <span class="badge badge-warning">In Progress</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($attempt->isCompleted()): ?>
                                                <strong><?= $attempt->band_score ?></strong>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?= Yii::$app->formatter->asDatetime($attempt->started_at, 'php:M d, Y H:i') ?></td>
                                        <td>
                                            <?php if ($attempt->isCompleted()): ?>
                                                <a href="<?= Url::to(['/test/result', 'id' => $attempt->id]) ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= Url::to(['/test/take', 'id' => $attempt->id]) ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-play"></i> Continue
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Available Tests -->
    <div class="row mt-4">
        <!-- Listening Tests -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-headphones"></i> Listening Tests</h5>
                    <a href="<?= Url::to(['/test/index', 'type' => 'listening']) ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($availableListeningTests)): ?>
                        <?php foreach (array_slice($availableListeningTests, 0, 3) as $test): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <h6><?= Html::encode($test->title) ?></h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-<?= $test->difficulty === 'easy' ? 'success' : ($test->difficulty === 'medium' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($test->difficulty) ?>
                                    </span>
                                    <span class="text-muted"><i class="fas fa-question-circle"></i> <?= $test->total_questions ?> questions</span>
                                    <a href="<?= Url::to(['/test/view', 'id' => $test->id]) ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-play"></i> Start
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No listening tests available yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Reading Tests -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-book-open"></i> Reading Tests</h5>
                    <a href="<?= Url::to(['/test/index', 'type' => 'reading']) ?>" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($availableReadingTests)): ?>
                        <?php foreach (array_slice($availableReadingTests, 0, 3) as $test): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <h6><?= Html::encode($test->title) ?></h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-<?= $test->difficulty === 'easy' ? 'success' : ($test->difficulty === 'medium' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($test->difficulty) ?>
                                    </span>
                                    <span class="text-muted"><i class="fas fa-question-circle"></i> <?= $test->total_questions ?> questions</span>
                                    <a href="<?= Url::to(['/test/view', 'id' => $test->id]) ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-play"></i> Start
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No reading tests available yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
