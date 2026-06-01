<?php
session_start();

$host = 'localhost';
$db   = 'uchu_rf';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Ошибка подключения к БД: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function require_login($redirect = 'application.php') {
    if (!is_logged_in()) {
        header('Location: login.php?redirect=' . urlencode($redirect));
        exit;
    }
}

function h($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function status_class($status) {
    if ($status === 'Новая') return 'new';
    if ($status === 'Идет обучение') return 'progress';
    return 'done';
}
