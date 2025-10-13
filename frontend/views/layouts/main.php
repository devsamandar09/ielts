<?php

/** @var \yii\web\View $this */
/** @var string $content */
use frontend\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\bootstrap4\Breadcrumbs;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - IELTS Practice Platform</title>
    <?php $this->head() ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f7fa;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 24px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 2px solid #f0f0f0;
            font-weight: 600;
        }
        .stats-card {
            border-left: 4px solid;
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-card.blue { border-left-color: #007bff; }
        .stats-card.green { border-left-color: #28a745; }
        .stats-card.orange { border-left-color: #fd7e14; }
        .stats-card.red { border-left-color: #dc3545; }
        .stats-card.purple { border-left-color: #6f42c1; }

        .test-card {
            transition: all 0.3s;
            cursor: pointer;
            height: 100%;
        }
        .test-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .btn-test {
            width: 100%;
            padding: 10px;
            font-weight: 600;
        }
        .progress-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(#007bff 0deg, #007bff var(--progress), #e9ecef var(--progress));
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 0 auto;
        }
        .progress-circle::before {
            content: '';
            position: absolute;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
        }
        .progress-value {
            position: relative;
            z-index: 1;
            font-size: 24px;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            padding: 30px 0;
            background-color: #fff;
            border-top: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
            'brandLabel' => '<i class="fas fa-graduation-cap"></i> IELTS Practice',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                    'class' => 'navbar navbar-expand-lg navbar-light bg-white shadow-sm',
            ],
    ]);

    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Home', 'url' => ['/site/index']];
        $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
        $menuItems[] = ['label' => 'Sign Up', 'url' => ['/site/signup'], 'options' => ['class' => 'btn btn-primary ml-2']];
    } else {
        $menuItems[] = ['label' => 'Dashboard', 'url' => ['/dashboard/index']];
        $menuItems[] = ['label' => 'Tests', 'url' => ['/test/index']];
        $menuItems[] = ['label' => 'History', 'url' => ['/dashboard/history']];
        $menuItems[] = ['label' => 'Progress', 'url' => ['/dashboard/progress']];
        $menuItems[] = '<li class="nav-item dropdown">'
                . '<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">'
                . '<i class="fas fa-user-circle"></i> ' . Html::encode(Yii::$app->user->identity->username)
                . '</a>'
                . '<div class="dropdown-menu dropdown-menu-right">'
                . '<a class="dropdown-item" href="' . Url::to(['/profile/index']) . '"><i class="fas fa-user"></i> Profile</a>'
                . '<div class="dropdown-divider"></div>'
                . Html::beginForm(['/site/logout'], 'post', ['class' => 'dropdown-item'])
                . Html::submitButton('<i class="fas fa-sign-out-alt"></i> Logout', ['class' => 'btn btn-link p-0'])
                . Html::endForm()
                . '</div>'
                . '</li>';
    }

    echo Nav::widget([
            'options' => ['class' => 'navbar-nav ml-auto'],
            'items' => $menuItems,
            'encodeLabels' => false,
    ]);

    NavBar::end();
    ?>

    <div class="container mt-4">
        <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= Yii::$app->session->getFlash('success') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= Yii::$app->session->getFlash('error') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p>&copy; <?= date('Y') ?> IELTS Practice Platform. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-right">
                <a href="#">Privacy Policy</a> |
                <a href="#">Terms of Service</a> |
                <a href="#">Contact Us</a>
            </div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
