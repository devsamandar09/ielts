<?php
use yii\helpers\Html;
use common\models\Question;

$questionData = $question->getQuestionData();
?>

<div class="question-card" data-question-id="<?= $question->id ?>">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <h5 class="mb-0">
            <span class="badge badge-primary">Q<?= $question->question_number ?></span>
            <?= Html::encode($question->getTypeLabel()) ?>
        </h5>
    </div>

    <?php if ($question->instruction): ?>
        <div class="alert alert-info">
            <strong>Instructions:</strong> <?= Html::encode($question->instruction) ?>
        </div>
    <?php endif; ?>

    <?php if ($question->question_text): ?>
        <p><strong><?= Html::encode($question->question_text) ?></strong></p>
    <?php endif; ?>

    <!-- Different question types -->
    <?php if ($question->type === Question::TYPE_MULTIPLE_CHOICE): ?>
        <!-- Multiple Choice -->
        <div class="form-group">
            <?php foreach ($questionData['options'] as $index => $option): ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio"
                           name="question_<?= $question->id ?>"
                           id="q<?= $question->id ?>_opt<?= $index ?>"
                           value="<?= substr($option, 0, 1) ?>"
                        <?= ($userAnswer && $userAnswer == substr($option, 0, 1)) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="q<?= $question->id ?>_opt<?= $index ?>">
                        <?= Html::encode($option) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($question->type === Question::TYPE_TRUE_FALSE_NOTGIVEN || $question->type === Question::TYPE_YES_NO_NOTGIVEN): ?>
        <!-- True/False/Not Given or Yes/No/Not Given -->
        <div class="form-group">
            <?php
            $options = $question->type === Question::TYPE_TRUE_FALSE_NOTGIVEN
                ? ['TRUE', 'FALSE', 'NOT GIVEN']
                : ['YES', 'NO', 'NOT GIVEN'];
            ?>
            <?php foreach ($options as $option): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio"
                           name="question_<?= $question->id ?>"
                           id="q<?= $question->id ?>_<?= strtolower(str_replace(' ', '_', $option)) ?>"
                           value="<?= $option ?>"
                        <?= ($userAnswer && strtolower($userAnswer) == strtolower($option)) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="q<?= $question->id ?>_<?= strtolower(str_replace(' ', '_', $option)) ?>">
                        <?= $option ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($question->type === Question::TYPE_SENTENCE_COMPLETION): ?>
        <!-- Sentence Completion -->
        <div class="form-group">
            <input type="text" class="form-control"
                   name="question_<?= $question->id ?>"
                   placeholder="Type your answer here"
                   value="<?= Html::encode($userAnswer ?? '') ?>">
        </div>

    <?php elseif ($question->type === Question::TYPE_FORM_COMPLETION): ?>
        <!-- Form Completion -->
        <div class="card">
            <div class="card-body">
                <?php foreach ($questionData['fields'] as $index => $field): ?>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">
                            <?= Html::encode($field['prompt']) ?>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control"
                                   name="question_<?= $question->id ?>_field_<?= $index ?>"
                                   placeholder="Answer"
                                   value="<?= Html::encode($userAnswer[$index] ?? '') ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php elseif ($question->type === Question::TYPE_MATCHING): ?>
        <!-- Matching -->
        <div class="form-group">
            <?php foreach ($questionData['items'] as $index => $item): ?>
                <div class="card mb-2">
                    <div class="card-body">
                        <p class="mb-2"><strong><?= Html::encode($item['left_side']) ?></strong></p>
                        <?php foreach ($item['options'] as $option): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="question_<?= $question->id ?>_item_<?= $index ?>"
                                       value="<?= substr($option, 0, 1) ?>"
                                    <?= ($userAnswer[$index] ?? '') == substr($option, 0, 1) ? 'checked' : '' ?>>
                                <label class="form-check-label">
                                    <?= Html::encode($option) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($question->type === Question::TYPE_MATCHING_HEADINGS): ?>
        <!-- Matching Headings -->
        <div class="alert alert-secondary">
            <strong>List of Headings:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($questionData['heading_options'] as $heading): ?>
                    <li><?= Html::encode($heading) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php foreach ($questionData['paragraphs'] as $para): ?>
            <div class="form-group">
                <label><strong>Paragraph <?= $para['paragraph_id'] ?>:</strong></label>
                <select class="form-control" name="question_<?= $question->id ?>_para_<?= $para['paragraph_id'] ?>">
                    <option value="">-- Select heading --</option>
                    <?php foreach ($questionData['heading_options'] as $heading): ?>
                        <option value="<?= substr($heading, 0, strpos($heading, ')')) ?>"
                            <?= ($userAnswer[$para['paragraph_id']] ?? '') == substr($heading, 0, strpos($heading, ')')) ? 'selected' : '' ?>>
                            <?= Html::encode($heading) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>

    <?php elseif ($question->type === Question::TYPE_SHORT_ANSWER): ?>
        <!-- Short Answer -->
        <div class="form-group">
            <input type="text" class="form-control"
                   name="question_<?= $question->id ?>"
                   placeholder="Write NO MORE THAN THREE WORDS"
                   value="<?= Html::encode($userAnswer ?? '') ?>">
        </div>

    <?php else: ?>
        <!-- Default fallback -->
        <div class="alert alert-warning">
            Question type not yet implemented: <?= Html::encode($question->type) ?>
        </div>
    <?php endif; ?>
</div>

