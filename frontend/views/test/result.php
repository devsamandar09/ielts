<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Test Results';
$this->params['breadcrumbs'][] = ['label' => 'Tests', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $test->title, 'url' => ['view', 'id' => $test->id]];
$this->params['breadcrumbs'][] = $this->title;

$correctCount = $attempt->total_correct;
$totalQuestions = $attempt->total_questions;
$percentage = round(($correctCount / $totalQuestions) * 100, 1);
?>

<style>
    .result-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 15px;
        text-align: center;
        margin-bottom: 30px;
    }
    .band-score-circle {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 20px auto;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .band-score-value {
        font-size: 72px;
        font-weight: bold;
        color: #667eea;
    }
    .band-score-label {
        font-size: 18px;
        color: #666;
        margin-top: -10px;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-item {
        background: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .stat-value {
        font-size: 36px;
        font-weight: bold;
        color: #667eea;
    }
    .stat-label {
        color: #666;
        margin-top: 5px;
    }
    .question-review {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s;
    }
    .question-review.correct {
        border-color: #28a745;
        background-color: #f8fff9;
    }
    .question-review.incorrect {
        border-color: #dc3545;
        background-color: #fff8f8;
    }
    .answer-comparison {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-top: 10px;
    }
    .performance-chart {
        height: 300px;
    }
</style>

<div class="test-result">
    <!-- Result Header -->
    <div class="result-header">
        <h1><i class="fas fa-trophy"></i> Test Completed!</h1>
        <div class="band-score-circle">
            <div class="band-score-value"><?= $attempt->band_score ?></div>
            <div class="band-score-label">Band Score</div>
        </div>
        <h3><?= Html::encode($test->title) ?></h3>
        <p class="mb-0">Completed on <?= Yii::$app->formatter->asDatetime($attempt->completed_at) ?></p>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-value"><?= $correctCount ?>/<?= $totalQuestions ?></div>
            <div class="stat-label">Correct Answers</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?= $percentage ?>%</div>
            <div class="stat-label">Accuracy</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?= $attempt->getTimeSpentFormatted() ?></div>
            <div class="stat-label">Time Spent</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?= $attempt->band_score ?></div>
            <div class="stat-label">IELTS Band</div>
        </div>
    </div>

    <!-- Band Score Interpretation -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5><i class="fas fa-info-circle"></i> Your Band Score Interpretation</h5>
        </div>
        <div class="card-body">
            <?php
            $bandScore = $attempt->band_score;
            if ($bandScore >= 8.5) {
                $interpretation = "Expert User - You have a fully operational command of the language with only occasional unsystematic inaccuracies.";
                $color = "success";
            } elseif ($bandScore >= 7.0) {
                $interpretation = "Good User - You have an operational command of the language, though with occasional inaccuracies.";
                $color = "info";
            } elseif ($bandScore >= 5.5) {
                $interpretation = "Competent/Modest User - You have a partial command of the language and generally handle overall meaning in familiar situations.";
                $color = "warning";
            } else {
                $interpretation = "Limited User - You have a basic competence in familiar situations but show frequent problems in understanding and expression.";
                $color = "danger";
            }
            ?>
            <div class="alert alert-<?= $color ?>">
                <strong>Band <?= $bandScore ?>:</strong> <?= $interpretation ?>
            </div>
        </div>
    </div>

    <!-- Performance Breakdown by Question Type -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-chart-pie"></i> Performance by Question Type</h5>
        </div>
        <div class="card-body">
            <?php
            $typeStats = [];
            foreach ($questionsWithAnswers as $item) {
                $question = $item['question'];
                $userAnswer = $item['userAnswer'];
                $type = $question->getTypeLabel();

                if (!isset($typeStats[$type])) {
                    $typeStats[$type] = ['correct' => 0, 'total' => 0];
                }

                $typeStats[$type]['total']++;
                if ($userAnswer && $userAnswer->is_correct) {
                    $typeStats[$type]['correct']++;
                }
            }
            ?>

            <div class="row">
                <?php foreach ($typeStats as $type => $stats): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6><?= $type ?></h6>
                                <div class="progress" style="height: 30px;">
                                    <?php
                                    $typePercentage = round(($stats['correct'] / $stats['total']) * 100, 1);
                                    $progressColor = $typePercentage >= 70 ? 'success' : ($typePercentage >= 50 ? 'warning' : 'danger');
                                    ?>
                                    <div class="progress-bar bg-<?= $progressColor ?>"
                                         style="width: <?= $typePercentage ?>%">
                                        <?= $stats['correct'] ?>/<?= $stats['total'] ?> (<?= $typePercentage ?>%)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-center mb-4">
        <a href="<?= Url::to(['/test/view', 'id' => $test->id]) ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-redo"></i> Retake This Test
        </a>
        <a href="<?= Url::to(['/test/index']) ?>" class="btn btn-info btn-lg">
            <i class="fas fa-list"></i> Browse More Tests
        </a>
        <a href="<?= Url::to(['/dashboard/index']) ?>" class="btn btn-secondary btn-lg">
            <i class="fas fa-home"></i> Back to Dashboard
        </a>
    </div>

    <!-- Detailed Question Review -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list-check"></i> Detailed Question Review</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <button class="btn btn-sm btn-outline-primary" onclick="showAll()">Show All</button>
                <button class="btn btn-sm btn-outline-success" onclick="showCorrect()">Show Correct Only</button>
                <button class="btn btn-sm btn-outline-danger" onclick="showIncorrect()">Show Incorrect Only</button>
            </div>

            <?php foreach ($questionsWithAnswers as $item): ?>
                <?php
                $question = $item['question'];
                $userAnswer = $item['userAnswer'];
                $isCorrect = $userAnswer && $userAnswer->is_correct;
                $correctAnswerData = $question->getCorrectAnswerData();
                ?>

                <div class="question-review <?= $isCorrect ? 'correct' : 'incorrect' ?>"
                     data-status="<?= $isCorrect ? 'correct' : 'incorrect' ?>">

                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6>
                                <span class="badge badge-secondary">Q<?= $question->question_number ?></span>
                                <?= Html::encode($question->getTypeLabel()) ?>
                            </h6>
                        </div>
                        <div>
                            <?php if ($isCorrect): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> Correct
                                </span>
                            <?php else: ?>
                                <span class="badge badge-danger">
                                    <i class="fas fa-times-circle"></i> Incorrect
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($question->instruction): ?>
                        <p class="text-muted"><em><?= Html::encode($question->instruction) ?></em></p>
                    <?php endif; ?>

                    <?php if ($question->question_text): ?>
                        <p><strong><?= Html::encode($question->question_text) ?></strong></p>
                    <?php endif; ?>

                    <div class="answer-comparison">
                        <div class="row">
                            <div class="col-md-6">
                                <strong class="text-primary">Your Answer:</strong>
                                <p class="mb-0">
                                    <?php if ($userAnswer): ?>
                                        <?= Html::encode(is_array($userAnswer->getUserAnswerData())
                                            ? json_encode($userAnswer->getUserAnswerData())
                                            : $userAnswer->getUserAnswerData()) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not answered</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong class="text-success">Correct Answer:</strong>
                                <p class="mb-0">
                                    <?= Html::encode(is_array($correctAnswerData)
                                        ? json_encode($correctAnswerData)
                                        : $correctAnswerData) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php if ($question->explanation): ?>
                        <div class="mt-3">
                            <button class="btn btn-sm btn-outline-info"
                                    onclick="$(this).next().slideToggle()">
                                <i class="fas fa-lightbulb"></i> Show Explanation
                            </button>
                            <div class="alert alert-info mt-2" style="display: none;">
                                <?= Html::encode($question->explanation) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5><i class="fas fa-lightbulb"></i> Recommendations for Improvement</h5>
        </div>
        <div class="card-body">
            <ul>
                <?php if ($percentage < 60): ?>
                    <li>Your score suggests you need more practice. Focus on understanding the question types better.</li>
                    <li>Review the basics of <?= $test->getTypeLabel() ?> test format and strategies.</li>
                <?php elseif ($percentage < 80): ?>
                    <li>You're doing well! Focus on the question types where you scored lowest.</li>
                    <li>Practice time management to improve your speed without losing accuracy.</li>
                <?php else: ?>
                    <li>Excellent work! You're performing at a high level.</li>
                    <li>Try harder difficulty tests to challenge yourself further.</li>
                <?php endif; ?>
                <li>Review all incorrect answers to understand your mistakes.</li>
                <li>Take regular practice tests to maintain and improve your performance.</li>
                <li>Consider setting a target band score and work systematically towards it.</li>
            </ul>
        </div>
    </div>
</div>

<?php
$js = <<<JS
function showAll() {
    $('.question-review').show();
}

function showCorrect() {
    $('.question-review').hide();
    $('.question-review[data-status="correct"]').show();
}

function showIncorrect() {
    $('.question-review').hide();
    $('.question-review[data-status="incorrect"]').show();
}

// Scroll animations
$(window).scroll(function() {
    $('.question-review').each(function() {
        var elementTop = $(this).offset().top;
        var viewportTop = $(window).scrollTop();
        var viewportBottom = viewportTop + $(window).height();
        
        if (elementTop < viewportBottom && elementTop > viewportTop) {
            $(this).css('opacity', '1');
        }
    });
});
JS;

$this->registerJs($js);
?>

