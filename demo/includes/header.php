<?php
$page_title = $page_title ?? 'Учусь.РФ';
$active_nav = $active_nav ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= h($page_title) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="wrapper header-inner">
        <a href="index.php" class="logo">Учусь<span>.РФ</span></a>
        <nav class="main-nav">
            <a href="index.php" class="<?= $active_nav === 'home' ? 'active' : '' ?>">Главная</a>
            <?php if (is_logged_in()): ?>
                <a href="cabinet.php" class="<?= $active_nav === 'cabinet' ? 'active' : '' ?>">Личный кабинет</a>
                <a href="application.php" class="<?= $active_nav === 'application' ? 'active' : '' ?>">Подать заявку</a>
                <span class="nav-user"><?= h($_SESSION['user_fio'] ?? '') ?></span>
                <a href="logout.php">Выход</a>
            <?php else: ?>
                <a href="login.php" class="<?= $active_nav === 'login' ? 'active' : '' ?>">Вход</a>
                <a href="register.php" class="nav-btn">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="page-main">
