<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $test->title;
$this->params['breadcrumbs'][] = ['label' => 'Tests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="test-view">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2><?= Html::encode($test->title) ?></h2>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-clipboard-list"></i> Type:</strong>
                                <span class="badge badge-<?= $test->type === 'listening' ? 'primary' : 'info' ?>">
                                    <?= $test->getTypeLabel() ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-signal"></i> Difficulty:</strong>
                                <span class="badge badge-<?= $test->difficulty === 'easy' ? 'success' : ($test->difficulty === 'medium' ? 'warning' : 'danger') ?>">
                                    <?= $test->getDifficultyLabel() ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-question-circle"></i> Questions:</strong> <?= $test->total_questions ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-clock"></i> Duration:</strong> <?= $test->duration ?> minutes</p>
                        </div>
                    </div>

                    <?php if ($test->description): ?>
                        <div class="mt-3">
                            <p><strong>Description:</strong></p>
                            <p><?= Html::encode($test->description) ?></p>
                        </div>
                    <?php endif; ?>

                    <hr>

                    <h5>Test Format:</h5>
                    <?php if ($test->isListening()): ?>
                        <ul>
                            <li>4 sections with different audio recordings</li>
                            <li>40 questions total</li>
                            <li>Various question types: Multiple choice, Form completion, Matching, Short answers</li>
                            <li>Audio plays only once</li>
                        </ul>
                    <?php else: ?>
                        <ul>
                            <li>3 reading passages</li>
                            <li>40 questions total</li>
                            <li>Question types: True/False/Not Given, Multiple choice, Matching headings, Sentence completion</li>
                            <li>60 minutes to complete</li>
                        </ul>
                    <?php endif; ?>

                    <?php if ($inProgressAttempt): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> You have an unfinished attempt for this test.
                            <a href="<?= Url::to(['/test/take', 'id' => $inProgressAttempt->id]) ?>" class="alert-link">Continue</a> or start a new attempt.
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <?= Html::beginForm(['start', 'id' => $test->id], 'post') ?>
                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-play-circle"></i> Start Test
                        </button>
                        <?= Html::endForm() ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Instructions -->
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5><i class="fas fa-exclamation-triangle"></i> Important Instructions</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Read all instructions carefully</li>
                        <li>Answer all questions</li>
                        <li>You can review and change answers before submitting</li>
                        <li>Timer will start once you begin</li>
                        <li>Submit when finished or time runs out</li>
                    </ul>
                </div>
            </div>

            <!-- Previous Attempts -->
            <?php if (!empty($previousAttempts)): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-history"></i> Your Previous Attempts</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($previousAttempts as $attempt): ?>
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <span><strong>Band Score:</strong> <?= $attempt->band_score ?></span>
                                    <span class="text-muted"><?= Yii::$app->formatter->asDate($attempt->completed_at, 'php:M d') ?></span>
                                </div>
                                <div>
                                    <small class="text-muted">
                                        <?= $attempt->total_correct ?>/<?= $attempt->total_questions ?> correct
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

