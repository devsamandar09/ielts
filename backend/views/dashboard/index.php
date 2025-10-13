<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="dashboard-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card stats-card blue">
                <div class="card-body">
                    <h5 class="card-title">Total Tests</h5>
                    <h2 class="card-text"><?= $stats['total_tests'] ?></h2>
                    <small class="text-muted"><?= $stats['published_tests'] ?> published</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card green">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h2 class="card-text"><?= $stats['total_users'] ?></h2>
                    <small class="text-muted">Registered students</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card orange">
                <div class="card-body">
                    <h5 class="card-title">Total Attempts</h5>
                    <h2 class="card-text"><?= $stats['total_attempts'] ?></h2>
                    <small class="text-muted"><?= $stats['completed_attempts'] ?> completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card red">
                <div class="card-body">
                    <h5 class="card-title">Completion Rate</h5>
                    <h2 class="card-text">
                        <?= $stats['total_attempts'] > 0
                            ? round(($stats['completed_attempts'] / $stats['total_attempts']) * 100, 1)
                            : 0 ?>%
                    </h2>
                    <small class="text-muted">Overall</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Distribution -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Test Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="testDistributionChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Average Band Scores</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Listening:</strong>
                        <span class="badge badge-primary"><?= $avgListeningScore ?: 'N/A' ?></span>
                    </div>
                    <div>
                        <strong>Reading:</strong>
                        <span class="badge badge-info"><?= $avgReadingScore ?: 'N/A' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Attempts Chart -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Test Attempts (Last 6 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyAttemptsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tests -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Tests</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recentTests as $test): ?>
                                <tr>
                                    <td><?= Html::encode($test->title) ?></td>
                                    <td><span class="badge badge-secondary"><?= $test->getTypeLabel() ?></span></td>
                                    <td><span class="badge badge-<?= $test->isPublished() ? 'success' : 'warning' ?>"><?= $test->getStatusLabel() ?></span></td>
                                    <td>
                                        <a href="<?= Url::to(['/test/view', 'id' => $test->id]) ?>" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Attempts -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Attempts</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>User</th>
                                <th>Test</th>
                                <th>Score</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recentAttempts as $attempt): ?>
                                <tr>
                                    <td><?= Html::encode($attempt->user->username) ?></td>
                                    <td><?= Html::encode($attempt->test->title) ?></td>
                                    <td>
                                        <?php if ($attempt->isCompleted()): ?>
                                            <span class="badge badge-success"><?= $attempt->band_score ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-warning"><?= $attempt->getStatusLabel() ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= Yii::$app->formatter->asDatetime($attempt->started_at) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$monthlyLabels = json_encode(array_column($monthlyData, 'month'));
$monthlyCounts = json_encode(array_column($monthlyData, 'count'));

$js = <<<JS
// Test Distribution Chart
var ctx1 = document.getElementById('testDistributionChart').getContext('2d');
new Chart(ctx1, {
    type: 'doughnut',
    data: {
        labels: ['Listening', 'Reading'],
        datasets: [{
            data: [{$listeningTests}, {$readingTests}],
            backgroundColor: ['#007bff', '#28a745']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Monthly Attempts Chart
var ctx2 = document.getElementById('monthlyAttemptsChart').getContext('2d');
new Chart(ctx2, {
    type: 'line',
    data: {
        labels: {$monthlyLabels},
        datasets: [{
            label: 'Test Attempts',
            data: {$monthlyCounts},
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4
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
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
JS;

$this->registerJs($js);
?>
