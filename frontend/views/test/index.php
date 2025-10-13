<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Available Tests';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="test-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-clipboard-list"></i> <?= Html::encode($this->title) ?></h1>
            <p class="lead">Choose a test and start practicing</p>
        </div>
        <div>
            <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-primary <?= !$type ? 'active' : '' ?>">
                All Tests
            </a>
            <a href="<?= Url::to(['index', 'type' => 'listening']) ?>"
               class="btn btn-outline-primary <?= $type === 'listening' ? 'active' : '' ?>">
                <i class="fas fa-headphones"></i> Listening
            </a>
            <a href="<?= Url::to(['index', 'type' => 'reading']) ?>"
               class="btn btn-outline-info <?= $type === 'reading' ? 'active' : '' ?>">
                <i class="fas fa-book-open"></i> Reading
            </a>
        </div>
    </div>

    <?php if (empty($tests)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-5x text-muted mb-3"></i>
                <h3>No Tests Available</h3>
                <p class="text-muted">Check back later for new tests</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($tests as $test): ?>
                <div class="col-md-4 mb-4">
                    <div class="card test-card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="mb-3">
                                <span class="badge badge-<?= $test->type === 'listening' ? 'primary' : 'info' ?> mr-2">
                                    <i class="fas fa-<?= $test->type === 'listening' ? 'headphones' : 'book-open' ?>"></i>
                                    <?= ucfirst($test->type) ?>
                                </span>
                                <span class="badge badge-<?= $test->difficulty === 'easy' ? 'success' : ($test->difficulty === 'medium' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($test->difficulty) ?>
                                </span>
                            </div>

                            <h5 class="card-title"><?= Html::encode($test->title) ?></h5>

                            <?php if ($test->description): ?>
                                <p class="card-text text-muted">
                                    <?= Html::encode(mb_substr($test->description, 0, 100)) ?>...
                                </p>
                            <?php endif; ?>

                            <div class="mt-auto">
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-question-circle"></i> <?= $test->total_questions ?> questions |
                                        <i class="fas fa-clock"></i> <?= $test->duration ?> min
                                    </small>
                                </div>

                                <a href="<?= Url::to(['view', 'id' => $test->id]) ?>"
                                   class="btn btn-<?= $test->type === 'listening' ? 'primary' : 'info' ?> btn-block">
                                    <i class="fas fa-play"></i> Start Test
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
