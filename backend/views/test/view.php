<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use common\models\Test;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Tests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="test-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?php if ($model->isDraft()): ?>
                <?= Html::a('<i class="fas fa-check"></i> Publish', ['publish', 'id' => $model->id], [
                    'class' => 'btn btn-success',
                    'data-method' => 'post',
                    'data-confirm' => 'Publish this test? It will be visible to all users.'
                ]) ?>
            <?php elseif ($model->isPublished()): ?>
                <?= Html::a('<i class="fas fa-archive"></i> Archive', ['archive', 'id' => $model->id], [
                    'class' => 'btn btn-warning',
                    'data-method' => 'post',
                    'data-confirm' => 'Archive this test? It will be hidden from users.'
                ]) ?>
            <?php endif; ?>

            <?= Html::a('<i class="fas fa-chart-bar"></i> Statistics', ['statistics', 'id' => $model->id], [
                'class' => 'btn btn-primary'
            ]) ?>

            <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data-method' => 'post',
                'data-confirm' => 'Are you sure you want to delete this test? This action cannot be undone.'
            ]) ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stats-card blue">
                <div class="card-body">
                    <h5 class="card-title">Total Attempts</h5>
                    <h2><?= $stats['total_attempts'] ?></h2>
                    <small class="text-muted"><?= $stats['completed_attempts'] ?> completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card green">
                <div class="card-body">
                    <h5 class="card-title">Average Score</h5>
                    <h2><?= $stats['average_score'] ?: 'N/A' ?></h2>
                    <small class="text-muted">Out of 100</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card orange">
                <div class="card-body">
                    <h5 class="card-title">Total Questions</h5>
                    <h2><?= $model->total_questions ?></h2>
                    <small class="text-muted"><?= $model->duration ?> minutes</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Details -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Test Details</h5>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'title',
                    [
                        'attribute' => 'type',
                        'value' => $model->getTypeLabel(),
                    ],
                    [
                        'attribute' => 'difficulty',
                        'value' => $model->getDifficultyLabel(),
                    ],
                    'description:ntext',
                    'duration',
                    'total_questions',
                    [
                        'attribute' => 'status',
                        'value' => $model->getStatusLabel(),
                    ],
                    [
                        'attribute' => 'created_by',
                        'value' => $model->creator ? $model->creator->username : 'Unknown',
                    ],
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
        </div>
    </div>

    <!-- Test Content -->
    <?php if ($model->isListening()): ?>
        <!-- Listening Sections -->
        <div class="card">
            <div class="card-header">
                <h5>Listening Sections</h5>
            </div>
            <div class="card-body">
                <?php foreach ($model->listeningSections as $section): ?>
                    <div class="border p-3 mb-3">
                        <h6><strong>Section <?= $section->section_number ?>: <?= Html::encode($section->getSectionTitle()) ?></strong></h6>
                        <p><strong>Context:</strong> <?= Html::encode($section->context) ?></p>
                        <p><strong>Duration:</strong> <?= $section->getAudioDurationFormatted() ?></p>

                        <?php if ($section->audio_url): ?>
                            <audio controls class="w-100 mb-2">
                                <source src="<?= $section->audio_url ?>" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                        <?php endif; ?>

                        <button class="btn btn-sm btn-secondary" type="button" data-toggle="collapse"
                                data-target="#transcript-<?= $section->id ?>">
                            Show Transcript
                        </button>

                        <div class="collapse mt-2" id="transcript-<?= $section->id ?>">
                            <div class="card card-body">
                                <?= nl2br(Html::encode($section->transcript)) ?>
                            </div>
                        </div>

                        <h6 class="mt-3">Questions:</h6>
                        <?php foreach ($section->questions as $question): ?>
                            <div class="ml-3 mb-2">
                                <strong>Q<?= $question->question_number ?>:</strong>
                                <?= Html::encode($question->question_text) ?>
                                <span class="badge badge-info"><?= $question->getTypeLabel() ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Reading Passages -->
        <div class="card">
            <div class="card-header">
                <h5>Reading Passages</h5>
            </div>
            <div class="card-body">
                <?php foreach ($model->readingPassages as $passage): ?>
                    <div class="border p-3 mb-3">
                        <h6><strong>Passage <?= $passage->passage_number ?>: <?= Html::encode($passage->getPassageTitle()) ?></strong></h6>
                        <p><strong>Word Count:</strong> <?= $passage->word_count ?></p>

                        <button class="btn btn-sm btn-secondary" type="button" data-toggle="collapse"
                                data-target="#passage-<?= $passage->id ?>">
                            Show Passage Text
                        </button>

                        <div class="collapse mt-2" id="passage-<?= $passage->id ?>">
                            <div class="card card-body" style="max-height: 400px; overflow-y: auto;">
                                <?= nl2br(Html::encode($passage->text)) ?>
                            </div>
                        </div>

                        <h6 class="mt-3">Questions:</h6>
                        <?php foreach ($passage->questions as $question): ?>
                            <div class="ml-3 mb-2">
                                <strong>Q<?= $question->question_number ?>:</strong>
                                <?= Html::encode($question->question_text) ?>
                                <span class="badge badge-info"><?= $question->getTypeLabel() ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

