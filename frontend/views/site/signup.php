<?php
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

$this->title = 'Sign Up';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-signup">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">
                        <i class="fas fa-user-plus"></i> Create Your Account
                    </h2>
                    <p class="text-center text-muted mb-4">Start your IELTS preparation journey today!</p>

                    <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'first_name')->textInput([
                                'autofocus' => true,
                                'placeholder' => 'John'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'last_name')->textInput([
                                'placeholder' => 'Doe'
                            ]) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'username')->textInput([
                        'placeholder' => 'Choose a username'
                    ]) ?>

                    <?= $form->field($model, 'email')->textInput([
                        'placeholder' => 'your.email@example.com'
                    ]) ?>

                    <?= $form->field($model, 'password')->passwordInput([
                        'placeholder' => 'Choose a strong password'
                    ]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('<i class="fas fa-user-plus"></i> Sign Up', [
                            'class' => 'btn btn-success btn-block btn-lg',
                            'name' => 'signup-button'
                        ]) ?>
                    </div>

                    <div class="text-center">
                        <p>Already have an account?
                            <?= Html::a('Login here', ['login']) ?>
                        </p>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>


