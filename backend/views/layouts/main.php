<?php

/** @var \yii\web\View $this */
/** @var string $content */
use backend\assets\AppAsset;
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
    <title><?= Html::encode($this->title) ?> - IELTS Admin</title>
    <?php $this->head() ?>
    <style>
        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #f8f9fa;
            width: 250px;
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 56px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .nav-link {
            color: #333;
            padding: 10px 20px;
        }
        .nav-link:hover {
            background-color: #e9ecef;
        }
        .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        .stats-card {
            border-left: 4px solid;
            margin-bottom: 20px;
        }
        .stats-card.blue { border-color: #007bff; }
        .stats-card.green { border-color: #28a745; }
        .stats-card.orange { border-color: #fd7e14; }
        .stats-card.red { border-color: #dc3545; }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
            'brandLabel' => 'IELTS Admin Panel',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                    'class' => 'navbar navbar-expand-lg navbar-dark bg-dark fixed-top',
            ],
    ]);

    $menuItems = [
            ['label' => 'Home', 'url' => ['/site/index']],
    ];

    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
    } else {
        $menuItems[] = '<li class="nav-item">'
                . Html::beginForm(['/site/logout'], 'post', ['class' => 'form-inline'])
                . Html::submitButton(
                        'Logout (' . Yii::$app->user->identity->username . ')',
                        ['class' => 'btn btn-link logout nav-link']
                )
                . Html::endForm()
                . '</li>';
    }

    echo Nav::widget([
            'options' => ['class' => 'navbar-nav ml-auto'],
            'items' => $menuItems,
    ]);

    NavBar::end();
    ?>

    <?php if (!Yii::$app->user->isGuest): ?>
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= Yii::$app->controller->id == 'dashboard' ? 'active' : '' ?>"
                           href="<?= Url::to(['/dashboard/index']) ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= Yii::$app->controller->id == 'test' ? 'active' : '' ?>"
                           href="<?= Url::to(['/test/index']) ?>">
                            <i class="fas fa-file-alt"></i> Tests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/test/create']) ?>">
                            <i class="fas fa-plus-circle"></i> Generate New Test
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= Yii::$app->controller->id == 'user' ? 'active' : '' ?>"
                           href="<?= Url::to(['/user/index']) ?>">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::to(['/site/logout']) ?>" data-method="post">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="<?= Yii::$app->user->isGuest ? 'container' : 'main-content' ?>">
        <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= Yii::$app->session->getFlash('success') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= Yii::$app->session->getFlash('error') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </div>
</div>

<?php $this->endBody() ?>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
<?php $this->endPage() ?>
