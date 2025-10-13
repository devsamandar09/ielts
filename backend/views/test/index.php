<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use common\models\Test;

$this->title = 'Tests';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="test-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <a href="<?= Url::to(['create']) ?>" class="btn btn-success">
            <i class="fas fa-plus"></i> Generate New Test
        </a>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'title',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::a(Html::encode($model->title), ['view', 'id' => $model->id]);
                }
            ],
            [
                'attribute' => 'type',
                'format' => 'raw',
                'value' => function($model) {
                    $class = $model->type === Test::TYPE_LISTENING ? 'primary' : 'info';
                    return '<span class="badge badge-' . $class . '">' . $model->getTypeLabel() . '</span>';
                }
            ],
            [
                'attribute' => 'difficulty',
                'format' => 'raw',
                'value' => function($model) {
                    $colors = [
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger'
                    ];
                    $color = $colors[$model->difficulty] ?? 'secondary';
                    return '<span class="badge badge-' . $color . '">' . $model->getDifficultyLabel() . '</span>';
                }
            ],
            [
                'attribute' => 'total_questions',
                'label' => 'Questions',
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function($model) {
                    $colors = [
                        Test::STATUS_DRAFT => 'secondary',
                        Test::STATUS_PUBLISHED => 'success',
                        Test::STATUS_ARCHIVED => 'dark'
                    ];
                    $color = $colors[$model->status] ?? 'secondary';
                    return '<span class="badge badge-' . $color . '">' . $model->getStatusLabel() . '</span>';
                }
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'php:Y-m-d H:i'],
                'label' => 'Created'
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {statistics} {delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'title' => 'View',
                            'class' => 'btn btn-sm btn-info'
                        ]);
                    },
                    'statistics' => function ($url, $model) {
                        return Html::a('<i class="fas fa-chart-bar"></i>', ['statistics', 'id' => $model->id], [
                            'title' => 'Statistics',
                            'class' => 'btn btn-sm btn-primary'
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'title' => 'Delete',
                            'class' => 'btn btn-sm btn-danger',
                            'data-confirm' => 'Are you sure you want to delete this test?',
                            'data-method' => 'post'
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
