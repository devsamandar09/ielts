<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Generate New Test';
$this->params['breadcrumbs'][] = ['label' => 'Tests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="test-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card mt-4">
        <div class="card-body">
            <form id="test-generation-form">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Test Type <span class="text-danger">*</span></label>
                            <select id="test-type" class="form-control" required>
                                <option value="">-- Select Type --</option>
                                <option value="listening">Listening</option>
                                <option value="reading">Reading</option>
                            </select>
                            <small class="form-text text-muted">Choose whether to generate a Listening or Reading test</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Difficulty Level <span class="text-danger">*</span></label>
                            <select id="difficulty" class="form-control" required>
                                <option value="easy">Easy</option>
                                <option value="medium" selected>Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                            <small class="form-text text-muted">Select the difficulty level for the test</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Topic (Optional)</label>
                    <input type="text" id="topic" class="form-control" placeholder="e.g., Technology, Environment, Education, Healthcare">
                    <small class="form-text text-muted">Specify a topic for the test content. Leave empty for general topics.</small>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Note:</strong> Test generation using AI may take 3-5 minutes.
                    Please be patient and do not close this page during generation.
                </div>

                <div class="form-group">
                    <button type="submit" id="generate-btn" class="btn btn-primary btn-lg">
                        <i class="fas fa-magic"></i> Generate Test with AI
                    </button>
                    <a href="<?= Url::to(['index']) ?>" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>

            <!-- Progress Section -->
            <div id="generation-progress" style="display: none;">
                <hr>
                <div class="alert alert-primary">
                    <h4><i class="fas fa-spinner fa-spin"></i> Generating test...</h4>
                    <p id="progress-message">Initializing AI test generator...</p>
                    <div class="progress" style="height: 30px;">
                        <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar" style="width: 0%; font-size: 16px;">
                            <span id="progress-text">0%</span>
                        </div>
                    </div>
                    <p class="mt-2 mb-0"><small>This process includes: AI content generation, question creation, and audio file generation for listening tests.</small></p>
                </div>
            </div>

            <!-- Result Section -->
            <div id="generation-result" style="display: none;"></div>
        </div>
    </div>
</div>

<?php
$generateUrl = Url::to(['generate']);
$viewUrl = Url::to(['view']);

$js = <<<JS
$('#test-generation-form').on('submit', function(e) {
    e.preventDefault();
    
    var type = $('#test-type').val();
    var difficulty = $('#difficulty').val();
    var topic = $('#topic').val();
    
    if (!type) {
        alert('Please select a test type');
        return;
    }
    
    if (!confirm('Generate new ' + type + ' test with ' + difficulty + ' difficulty?\\n\\nThis will take several minutes. Continue?')) {
        return;
    }
    
    // Disable form
    $('#generate-btn').prop('disabled', true);
    $('#test-type, #difficulty, #topic').prop('disabled', true);
    
    // Show progress
    $('#generation-progress').show();
    $('#generation-result').hide();
    
    // Progress messages
    var progressMessages = [
        'Connecting to AI service...',
        'Generating test content...',
        'Creating questions...',
        'Generating audio files...',
        'Finalizing test...'
    ];
    var messageIndex = 0;
    var currentProgress = 0;
    
    // Simulate progress
    var progressInterval = setInterval(function() {
        currentProgress = Math.min(currentProgress + (Math.random() * 15), 95);
        
        $('#progress-bar').css('width', currentProgress + '%');
        $('#progress-text').text(Math.round(currentProgress) + '%');
        
        if (messageIndex < progressMessages.length && currentProgress > (messageIndex + 1) * 20) {
            $('#progress-message').text(progressMessages[messageIndex]);
            messageIndex++;
        }
    }, 3000);
    
    // Make AJAX request
    $.ajax({
        url: '{$generateUrl}',
        type: 'POST',
        data: {
            type: type,
            difficulty: difficulty,
            topic: topic
        },
        timeout: 600000, // 10 minutes
        success: function(response) {
            clearInterval(progressInterval);
            
            $('#progress-bar').css('width', '100%');
            $('#progress-text').text('100%');
            $('#progress-message').text('Test generated successfully!');
            
            setTimeout(function() {
                if (response.success) {
                    $('#generation-result').html(
                        '<div class="alert alert-success">' +
                        '<h4><i class="fas fa-check-circle"></i> Success!</h4>' +
                        '<p>' + response.message + '</p>' +
                        '<a href="{$viewUrl}?id=' + response.test_id + '" class="btn btn-success">' +
                        '<i class="fas fa-eye"></i> View Generated Test</a> ' +
                        '<a href="' + window.location.href + '" class="btn btn-primary">' +
                        '<i class="fas fa-plus"></i> Generate Another Test</a>' +
                        '</div>'
                    ).show();
                    
                    $('#generation-progress').fadeOut();
                } else {
                    $('#generation-result').html(
                        '<div class="alert alert-danger">' +
                        '<h4><i class="fas fa-exclamation-circle"></i> Generation Failed</h4>' +
                        '<p>' + response.message + '</p>' +
                        '<button class="btn btn-primary" onclick="location.reload()">' +
                        '<i class="fas fa-redo"></i> Try Again</button>' +
                        '</div>'
                    ).show();
                    
                    $('#generation-progress').fadeOut();
                    
                    // Re-enable form
                    $('#generate-btn').prop('disabled', false);
                    $('#test-type, #difficulty, #topic').prop('disabled', false);
                }
            }, 1000);
        },
        error: function(xhr, status, error) {
            clearInterval(progressInterval);
            
            var errorMessage = 'An error occurred during test generation.';
            if (status === 'timeout') {
                errorMessage = 'Request timed out. The test generation took too long.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            $('#generation-result').html(
                '<div class="alert alert-danger">' +
                '<h4><i class="fas fa-exclamation-circle"></i> Error</h4>' +
                '<p>' + errorMessage + '</p>' +
                '<button class="btn btn-primary" onclick="location.reload()">' +
                '<i class="fas fa-redo"></i> Try Again</button>' +
                '</div>'
            ).show();
            
            $('#generation-progress').fadeOut();
            
            // Re-enable form
            $('#generate-btn').prop('disabled', false);
            $('#test-type, #difficulty, #topic').prop('disabled', false);
        }
    });
});
JS;

$this->registerJs($js);
?>





