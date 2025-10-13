<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;

$this->title = 'Taking Test: ' . $test->title;
$this->params['breadcrumbs'][] = ['label' => 'Tests', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $test->title, 'url' => ['view', 'id' => $test->id]];
$this->params['breadcrumbs'][] = 'Take Test';

$isListening = $test->isListening();
?>

<style>
    .test-container {
        background: white;
        border-radius: 10px;
        padding: 20px;
    }
    .question-card {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        transition: border-color 0.3s;
    }
    .question-card.answered {
        border-color: #28a745;
        background-color: #f8fff9;
    }
    .timer-box {
        position: sticky;
        top: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }
    .timer-display {
        font-size: 36px;
        font-weight: bold;
    }
    .audio-player-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .progress-indicator {
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .question-nav {
        display: grid;
        grid-template-columns: repeat(10, 1fr);
        gap: 10px;
    }
    .question-nav-btn {
        padding: 10px;
        border: 2px solid #dee2e6;
        background: white;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .question-nav-btn:hover {
        border-color: #007bff;
    }
    .question-nav-btn.answered {
        background: #28a745;
        color: white;
        border-color: #28a745;
    }
    .question-nav-btn.active {
        border-color: #007bff;
        border-width: 3px;
    }
</style>

<div class="test-take">
    <div class="row">
        <!-- Main Test Area -->
        <div class="col-md-9">
            <div class="test-container">
                <!-- Test Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3><?= Html::encode($test->title) ?></h3>
                        <p class="text-muted mb-0">
                            <i class="fas fa-question-circle"></i> <?= $test->total_questions ?> Questions |
                            <i class="fas fa-clock"></i> <?= $test->duration ?> Minutes
                        </p>
                    </div>
                    <div>
                        <span class="badge badge-info" id="answered-count">0</span> / <?= $test->total_questions ?> Answered
                    </div>
                </div>

                <!-- Listening Audio Player (if listening test) -->
                <?php if ($isListening): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-volume-up"></i> <strong>Audio Instructions:</strong>
                        The audio for each section will play automatically. Listen carefully as it plays only once.
                        You can pause and replay within each section.
                    </div>
                <?php endif; ?>

                <!-- Test Content -->
                <div id="test-content">
                    <?php if ($isListening): ?>
                        <!-- Listening Sections -->
                        <?php foreach ($test->listeningSections as $sectionIndex => $section): ?>
                            <div class="section-container" data-section="<?= $section->id ?>"
                                 style="<?= $sectionIndex > 0 ? 'display: none;' : '' ?>">

                                <div class="section-header">
                                    <h4><i class="fas fa-headphones"></i> Section <?= $section->section_number ?></h4>
                                    <p class="mb-0"><?= Html::encode($section->context) ?></p>
                                </div>

                                <?php if ($section->audio_url): ?>
                                    <div class="audio-player-container">
                                        <audio id="audio-section-<?= $section->id ?>" controls class="w-100" controlsList="nodownload">
                                            <source src="<?= $section->audio_url ?>" type="audio/mpeg">
                                            Your browser does not support the audio element.
                                        </audio>
                                        <div class="mt-2 text-center">
                                            <button class="btn btn-sm btn-primary" onclick="document.getElementById('audio-section-<?= $section->id ?>').currentTime = 0; document.getElementById('audio-section-<?= $section->id ?>').play();">
                                                <i class="fas fa-redo"></i> Replay Audio
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Section Questions -->
                                <?php foreach ($section->questions as $question): ?>
                                    <?= $this->render('_question', [
                                        'question' => $question,
                                        'attemptId' => $attempt->id,
                                        'userAnswer' => $userAnswers[$question->id] ?? null
                                    ]) ?>
                                <?php endforeach; ?>

                                <?php if ($sectionIndex < count($test->listeningSections) - 1): ?>
                                    <button class="btn btn-primary btn-lg" onclick="nextSection(<?= $section->id ?>)">
                                        <i class="fas fa-arrow-right"></i> Next Section
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Reading Passages -->
                        <?php foreach ($test->readingPassages as $passageIndex => $passage): ?>
                            <div class="passage-container" data-passage="<?= $passage->id ?>"
                                 style="<?= $passageIndex > 0 ? 'display: none;' : '' ?>">

                                <div class="section-header">
                                    <h4><i class="fas fa-book-open"></i> Passage <?= $passage->passage_number ?>:
                                        <?= Html::encode($passage->getPassageTitle()) ?>
                                    </h4>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-body" style="max-height: 400px; overflow-y: auto; line-height: 1.8;">
                                        <?= nl2br(Html::encode($passage->text)) ?>
                                    </div>
                                </div>

                                <!-- Passage Questions -->
                                <?php foreach ($passage->questions as $question): ?>
                                    <?= $this->render('_question', [
                                        'question' => $question,
                                        'attemptId' => $attempt->id,
                                        'userAnswer' => $userAnswers[$question->id] ?? null
                                    ]) ?>
                                <?php endforeach; ?>

                                <?php if ($passageIndex < count($test->readingPassages) - 1): ?>
                                    <button class="btn btn-primary btn-lg" onclick="nextPassage(<?= $passage->id ?>)">
                                        <i class="fas fa-arrow-right"></i> Next Passage
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <div class="mt-4 text-center">
                    <button id="submit-test-btn" class="btn btn-success btn-lg" onclick="submitTest()">
                        <i class="fas fa-check-circle"></i> Submit Test
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-3">
            <!-- Timer -->
            <div class="timer-box">
                <div><i class="fas fa-clock"></i></div>
                <div class="timer-display" id="timer">
                    <span id="minutes"><?= $test->duration ?></span>:<span id="seconds">00</span>
                </div>
                <div class="mt-2">Time Remaining</div>
            </div>

            <!-- Progress Indicator -->
            <div class="progress-indicator mt-3">
                <h6><i class="fas fa-tasks"></i> Question Navigator</h6>
                <div class="question-nav" id="question-navigator">
                    <?php foreach ($test->questions as $index => $question): ?>
                        <button class="question-nav-btn" data-question="<?= $question->id ?>"
                                onclick="scrollToQuestion(<?= $question->id ?>)">
                            <?= $index + 1 ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Legend -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6>Legend:</h6>
                    <div class="mb-2">
                        <span class="badge badge-light border">Not Answered</span>
                    </div>
                    <div class="mb-2">
                        <span class="badge badge-success">Answered</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$attemptId = $attempt->id;
$testDuration = $test->duration;
$saveAnswerUrl = Url::to(['save-answer']);
$submitUrl = Url::to(['submit', 'id' => $attemptId]);
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
var attemptId = {$attemptId};
var timeRemaining = {$testDuration} * 60; // in seconds
var timerInterval;
var answeredQuestions = new Set();

// Initialize
$(document).ready(function() {
    startTimer();
    updateAnsweredCount();
    
    // Load existing answers
    $('.question-card').each(function() {
        var questionId = $(this).data('question-id');
        if (hasAnswer(questionId)) {
            markQuestionAsAnswered(questionId);
        }
    });
});

// Timer
function startTimer() {
    timerInterval = setInterval(function() {
        timeRemaining--;
        
        var minutes = Math.floor(timeRemaining / 60);
        var seconds = timeRemaining % 60;
        
        $('#minutes').text(minutes < 10 ? '0' + minutes : minutes);
        $('#seconds').text(seconds < 10 ? '0' + seconds : seconds);
        
        // Warning when 5 minutes left
        if (timeRemaining === 300) {
            alert('Warning: Only 5 minutes remaining!');
        }
        
        // Auto submit when time is up
        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            alert('Time is up! Submitting your test...');
            submitTest();
        }
    }, 1000);
}

// Save answer
function saveAnswer(questionId, answer) {
    $.ajax({
        url: '{$saveAnswerUrl}',
        type: 'POST',
        data: {
            _csrf: '{$csrfToken}',
            attempt_id: attemptId,
            question_id: questionId,
            answer: answer
        },
        success: function(response) {
            if (response.success) {
                markQuestionAsAnswered(questionId);
                updateAnsweredCount();
            }
        }
    });
}

// Mark question as answered
function markQuestionAsAnswered(questionId) {
    answeredQuestions.add(questionId);
    $('.question-card[data-question-id="' + questionId + '"]').addClass('answered');
    $('.question-nav-btn[data-question="' + questionId + '"]').addClass('answered');
}

// Update answered count
function updateAnsweredCount() {
    $('#answered-count').text(answeredQuestions.size);
}

// Check if question has answer
function hasAnswer(questionId) {
    var card = $('.question-card[data-question-id="' + questionId + '"]');
    var inputs = card.find('input[type="radio"]:checked, input[type="text"][value!=""], textarea[value!=""]');
    return inputs.length > 0;
}

// Handle input changes
$(document).on('change', 'input[type="radio"], input[type="checkbox"]', function() {
    var questionId = $(this).closest('.question-card').data('question-id');
    var answer = $(this).val();
    saveAnswer(questionId, answer);
});

$(document).on('blur', 'input[type="text"], textarea', function() {
    var questionId = $(this).closest('.question-card').data('question-id');
    var answer = $(this).val().trim();
    if (answer) {
        saveAnswer(questionId, answer);
    }
});

// Navigation functions
function nextSection(currentSectionId) {
    $('.section-container[data-section="' + currentSectionId + '"]').hide();
    $('.section-container[data-section="' + currentSectionId + '"]').next('.section-container').show();
    window.scrollTo(0, 0);
}

function nextPassage(currentPassageId) {
    $('.passage-container[data-passage="' + currentPassageId + '"]').hide();
    $('.passage-container[data-passage="' + currentPassageId + '"]').next('.passage-container').show();
    window.scrollTo(0, 0);
}

function scrollToQuestion(questionId) {
    var element = $('.question-card[data-question-id="' + questionId + '"]');
    if (element.length) {
        $('html, body').animate({
            scrollTop: element.offset().top - 100
        }, 500);
        element.css('border-color', '#007bff');
        setTimeout(function() {
            element.css('border-color', '');
        }, 2000);
    }
}

// Submit test
function submitTest() {
    if (!confirm('Are you sure you want to submit this test? You cannot change your answers after submission.')) {
        return;
    }
    
    clearInterval(timerInterval);
    $('#submit-test-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
    
    $.ajax({
        url: '{$submitUrl}',
        type: 'POST',
        data: {
            _csrf: '{$csrfToken}'
        },
        success: function() {
            window.location.href = '{$submitUrl}'.replace('/submit/', '/result/');
        },
        error: function() {
            alert('Error submitting test. Please try again.');
            $('#submit-test-btn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Submit Test');
        }
    });
}

// Prevent accidental page leave
window.onbeforeunload = function() {
    return "Are you sure you want to leave? Your progress will be saved but the timer will continue.";
};
JS;

$this->registerJs($js);
?>

