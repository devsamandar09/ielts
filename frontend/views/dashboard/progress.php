<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;

$this->title = 'My Progress';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Prepare data for charts
$listeningChartData = Json::encode($listeningData);
$readingChartData = Json::encode($readingData);
?>

<div class="progress-page">
    <h1><i class="fas fa-chart-line"></i> <?= Html::encode($this->title) ?></h1>
    <p class="lead">Track your improvement over time</p>

    <?php if (empty($attempts)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-chart-line fa-5x text-muted mb-3"></i>
                <h3>No Progress Data Yet</h3>
                <p class="text-muted">Complete some tests to see your progress</p>
                <a href="<?= Url::to(['/test/index']) ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-play"></i> Start Testing
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Overall Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card blue">
                    <div class="card-body text-center">
                        <i class="fas fa-clipboard-check fa-3x text-primary mb-2"></i>
                        <h3><?= count($attempts) ?></h3>
                        <p class="text-muted mb-0">Total Tests</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card green">
                    <div class="card-body text-center">
                        <i class="fas fa-star fa-3x text-success mb-2"></i>
                        <?php
                        $avgScore = array_sum(array_column($attempts, 'band_score')) / count($attempts);
                        ?>
                        <h3><?= round($avgScore, 1) ?></h3>
                        <p class="text-muted mb-0">Average Band</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card orange">
                    <div class="card-body text-center">
                        <i class="fas fa-arrow-up fa-3x text-warning mb-2"></i>
                        <?php
                        $maxScore = max(array_column($attempts, 'band_score'));
                        ?>
                        <h3><?= $maxScore ?></h3>
                        <p class="text-muted mb-0">Best Score</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card purple">
                    <div class="card-body text-center">
                        <i class="fas fa-fire fa-3x text-danger mb-2"></i>
                        <?php
                        // Calculate improvement (difference between first and last score)
                        $firstScore = $attempts[count($attempts) - 1]->band_score ?? 0;
                        $lastScore = $attempts[0]->band_score ?? 0;
                        $improvement = $lastScore - $firstScore;
                        ?>
                        <h3><?= $improvement >= 0 ? '+' : '' ?><?= round($improvement, 1) ?></h3>
                        <p class="text-muted mb-0">Improvement</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Score Progression Charts -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-headphones"></i> Listening Progress</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($listeningData)): ?>
                            <canvas id="listeningChart"></canvas>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-info-circle fa-3x mb-3"></i>
                                <p>No listening test data available</p>
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
                        <?php if (!empty($readingData)): ?>
                            <canvas id="readingChart"></canvas>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-info-circle fa-3x mb-3"></i>
                                <p>No reading test data available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance by Difficulty -->
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-signal"></i> Performance by Difficulty Level</h5>
            </div>
            <div class="card-body">
                <canvas id="difficultyChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity Timeline -->
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach (array_slice($attempts, 0, 10) as $attempt): ?>
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-icon">
                                    <i class="fas fa-<?= $attempt->test->type === 'listening' ? 'headphones' : 'book-open' ?>
                                       text-<?= $attempt->test->type === 'listening' ? 'primary' : 'info' ?>"></i>
                                </div>
                                <div class="timeline-content ml-3 flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?= Html::encode($attempt->test->title) ?></strong>
                                            <p class="text-muted mb-0">
                                                <small><?= Yii::$app->formatter->asRelativeTime($attempt->completed_at) ?></small>
                                            </p>
                                        </div>
                                        <div>
                                            <span class="badge badge-success">Band <?= $attempt->band_score ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Strengths and Weaknesses -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-thumbs-up"></i> Your Strengths</h5>
                    </div>
                    <div class="card-body">
                        <ul>
                            <?php
                            $avgListening = !empty($listeningData) ? array_sum(array_column($listeningData, 'score')) / count($listeningData) : 0;
                            $avgReading = !empty($readingData) ? array_sum(array_column($readingData, 'score')) / count($readingData) : 0;
                            ?>
                            <?php if ($avgListening > $avgReading): ?>
                                <li><strong>Listening:</strong> You perform better in listening tests</li>
                            <?php else: ?>
                                <li><strong>Reading:</strong> You perform better in reading tests</li>
                            <?php endif; ?>

                            <?php if ($improvement > 0): ?>
                                <li><strong>Improvement:</strong> You've shown consistent improvement over time</li>
                            <?php endif; ?>

                            <?php if ($maxScore >= 7.0): ?>
                                <li><strong>High Achiever:</strong> You've achieved a high band score</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5><i class="fas fa-exclamation-triangle"></i> Areas for Improvement</h5>
                    </div>
                    <div class="card-body">
                        <ul>
                            <?php if ($avgListening < $avgReading): ?>
                                <li>Focus more on <strong>Listening</strong> practice</li>
                            <?php else: ?>
                                <li>Focus more on <strong>Reading</strong> practice</li>
                            <?php endif; ?>

                            <?php if ($avgScore < 6.5): ?>
                                <li>Work on improving your overall band score</li>
                            <?php endif; ?>

                            <?php if (count($attempts) < 10): ?>
                                <li>Take more practice tests for better accuracy</li>
                            <?php endif; ?>

                            <li>Review incorrect answers to understand your mistakes</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
    ['depends' => [\yii\web\JqueryAsset::class]]);

$js = <<<JS
// Listening Progress Chart
var listeningData = {$listeningChartData};
if (listeningData.length > 0 && document.getElementById('listeningChart')) {
    var ctx1 = document.getElementById('listeningChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: listeningData.map(d => d.date),
            datasets: [{
                label: 'Band Score',
                data: listeningData.map(d => d.score),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 9,
                    ticks: {
                        stepSize: 0.5
                    }
                }
            }
        }
    });
}

// Reading Progress Chart
var readingData = {$readingChartData};
if (readingData.length > 0 && document.getElementById('readingChart')) {
    var ctx2 = document.getElementById('readingChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: readingData.map(d => d.date),
            datasets: [{
                label: 'Band Score',
                data: readingData.map(d => d.score),
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 9,
                    ticks: {
                        stepSize: 0.5
                    }
                }
            }
        }
    });
}

// Difficulty Performance Chart
if (document.getElementById('difficultyChart')) {
    // Calculate average scores by difficulty
    var difficulties = {easy: [], medium: [], hard: []};
    
    // You would populate this from PHP data
    var ctx3 = document.getElementById('difficultyChart').getContext('2d');
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: ['Easy', 'Medium', 'Hard'],
            datasets: [{
                label: 'Average Band Score',
                data: [7.5, 6.5, 5.5], // Example data
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(220, 53, 69, 0.7)'
                ],
                borderColor: [
                    'rgb(40, 167, 69)',
                    'rgb(255, 193, 7)',
                    'rgb(220, 53, 69)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 9,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}
JS;

$this->registerJs($js);
?>

