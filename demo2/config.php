<?php
session_start();

$conn = mysqli_connect('localhost', 'root', '', 'uchu_rf');
if (!$conn) die('Ошибка БД: ' . mysqli_connect_error());
mysqli_set_charset($conn, 'utf8mb4');

function h($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function head($title = 'Учусь.РФ') {
    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8">';
    echo '<title>', h($title), '</title><link rel="stylesheet" href="css/style.css"></head><body>';
    echo '<header><div class="wrap"><a href="index.php"><b>Учусь.РФ</b></a> ';
    if (is_logged_in()) {
        echo '<a href="cabinet.php">Кабинет</a> <a href="application.php">Заявка</a> <a href="logout.php">Выход</a>';
    } else {
        echo '<a href="login.php">Вход</a> <a href="register.php">Регистрация</a>';
    }
    echo '</div></header><div class="wrap">';
}

function foot() {
    echo '</div><footer class="foot"><a href="admin.php">Админ</a></footer></body></html>';
}
