<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-login">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">
                        <i class="fas fa-sign-in-alt"></i> Login to Your Account
                    </h2>

                    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                    <?= $form->field($model, 'username')->textInput([
                            'autofocus' => true,
                            'placeholder' => 'Enter your username'
                    ])->label('Username') ?>

                    <?= $form->field($model, 'password')->passwordInput([
                            'placeholder' => 'Enter your password'
                    ])->label('Password') ?>

                    <?= $form->field($model, 'rememberMe')->checkbox() ?>

                    <div class="form-group">
                        <?= Html::submitButton('<i class="fas fa-sign-in-alt"></i> Login', [
                                'class' => 'btn btn-primary btn-block btn-lg',
                                'name' => 'login-button'
                        ]) ?>
                    </div>

                    <div class="text-center">
                        <p>Don't have an account?
                            <?= Html::a('Sign up here', ['signup']) ?>
                        </p>
                        <p>
                            <?= Html::a('Forgot password?', ['request-password-reset']) ?>
                        </p>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

