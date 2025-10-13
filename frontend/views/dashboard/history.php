<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Test History';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="test-history">
    <h1><i class="fas fa-history"></i> <?= Html::encode($this->title) ?></h1>
    <p class="lead">View all your completed and in-progress tests</p>

    <?php if (empty($attempts)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-clipboard-list fa-5x text-muted mb-3"></i>
                <h3>No Test History Yet</h3>
                <p class="text-muted">Start taking tests to see your history here</p>
                <a href="<?= Url::to(['/test/index']) ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-play"></i> Browse Tests
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Summary Cards -->
        <?php
        $totalTests = count($attempts);
        $completedTests = count(array_filter($attempts, function($a) { return $a->isCompleted(); }));
        $averageScore = 0;
        if ($completedTests > 0) {
            $totalScore = array_sum(array_map(function($a) {
                return $a->isCompleted() ? $a->band_score : 0;
            }, $attempts));
            $averageScore = round($totalScore / $completedTests, 1);
        }
        ?>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stats-card blue">
                    <div class="card-body text-center">
                        <i class="fas fa-clipboard-check fa-3x text-primary mb-2"></i>
                        <h3><?= $completedTests ?></h3>
                        <p class="text-muted mb-0">Completed Tests</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card green">
                    <div class="card-body text-center">
                        <i class="fas fa-spinner fa-3x text-warning mb-2"></i>
                        <h3><?= $totalTests - $completedTests ?></h3>
                        <p class="text-muted mb-0">In Progress</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card purple">
                    <div class="card-body text-center">
                        <i class="fas fa-star fa-3x text-success mb-2"></i>
                        <h3><?= $averageScore ?: 'N/A' ?></h3>
                        <p class="text-muted mb-0">Average Band Score</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-control" id="filter-type">
                            <option value="all">All Types</option>
                            <option value="listening">Listening</option>
                            <option value="reading">Reading</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="filter-status">
                            <option value="all">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="in_progress">In Progress</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="sort-by">
                            <option value="date-desc">Date (Newest First)</option>
                            <option value="date-asc">Date (Oldest First)</option>
                            <option value="score-desc">Score (Highest First)</option>
                            <option value="score-asc">Score (Lowest First)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test History List -->
        <div id="history-list">
            <?php foreach ($attempts as $attempt): ?>
                <div class="card mb-3 test-history-item"
                     data-type="<?= $attempt->test->type ?>"
                     data-status="<?= $attempt->status ?>"
                     data-score="<?= $attempt->band_score ?? 0 ?>"
                     data-date="<?= $attempt->started_at ?>">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-2">
                                    <i class="fas fa-<?= $attempt->test->type === 'listening' ? 'headphones' : 'book-open' ?>"></i>
                                    <?= Html::encode($attempt->test->title) ?>
                                </h5>
                                <div class="mb-2">
                                    <span class="badge badge-<?= $attempt->test->type === 'listening' ? 'primary' : 'info' ?>">
                                        <?= ucfirst($attempt->test->type) ?>
                                    </span>
                                    <span class="badge badge-<?= $attempt->test->difficulty === 'easy' ? 'success' : ($attempt->test->difficulty === 'medium' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($attempt->test->difficulty) ?>
                                    </span>
                                </div>
                                <p class="text-muted mb-0">
                                    <small>
                                        <i class="fas fa-calendar"></i>
                                        Started: <?= Yii::$app->formatter->asDatetime($attempt->started_at) ?>
                                    </small>
                                </p>
                            </div>

                            <div class="col-md-3 text-center">
                                <?php if ($attempt->isCompleted()): ?>
                                    <div class="mb-2">
                                        <h3 class="mb-0 text-primary"><?= $attempt->band_score ?></h3>
                                        <small class="text-muted">Band Score</small>
                                    </div>
                                    <div>
                                        <strong><?= $attempt->total_correct ?>/<?= $attempt->total_questions ?></strong>
                                        <small class="text-muted">correct</small>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-warning p-3">
                                        <i class="fas fa-spinner"></i> In Progress
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-3 text-right">
                                <?php if ($attempt->isCompleted()): ?>
                                    <a href="<?= Url::to(['/test/result', 'id' => $attempt->id]) ?>"
                                       class="btn btn-info">
                                        <i class="fas fa-eye"></i> View Results
                                    </a>
                                    <a href="<?= Url::to(['/test/view', 'id' => $attempt->test_id]) ?>"
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-redo"></i> Retake
                                    </a>
                                <?php else: ?>
                                    <a href="<?= Url::to(['/test/take', 'id' => $attempt->id]) ?>"
                                       class="btn btn-success">
                                        <i class="fas fa-play"></i> Continue
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$js = <<<JS
// Filter and sort functionality
$('#filter-type, #filter-status, #sort-by').on('change', function() {
    filterAndSort();
});

function filterAndSort() {
    var filterType = $('#filter-type').val();
    var filterStatus = $('#filter-status').val();
    var sortBy = $('#sort-by').val();
    
    var items = $('.test-history-item').toArray();
    
    // Filter
    items.forEach(function(item) {
        var showType = filterType === 'all' || $(item).data('type') === filterType;
        var showStatus = filterStatus === 'all' || $(item).data('status') === filterStatus;
        
        if (showType && showStatus) {
            $(item).show();
        } else {
            $(item).hide();
        }
    });
    
    // Sort
    var visibleItems = $('.test-history-item:visible').toArray();
    
    visibleItems.sort(function(a, b) {
        var aVal, bVal;
        
        switch(sortBy) {
            case 'date-desc':
                return $(b).data('date') - $(a).data('date');
            case 'date-asc':
                return $(a).data('date') - $(b).data('date');
            case 'score-desc':
                return $(b).data('score') - $(a).data('score');
            case 'score-asc':
                return $(a).data('score') - $(b).data('score');
        }
    });
    
    // Reorder
    var container = $('#history-list');
    container.append(visibleItems);
}
JS;

$this->registerJs($js);
?>

