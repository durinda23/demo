<?php
require_once 'config.php';

$error = '';
$redirect = $_GET['redirect'] ?? 'index.php';
if (!preg_match('/^[a-z_]+\.php$/', $redirect)) {
    $redirect = 'index.php';
}

if (!empty($_GET['registered'])) {
    $success = 'Регистрация прошла успешно. Войдите в систему.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? 'index.php';
    if (!preg_match('/^[a-z_]+\.php$/', $redirect)) {
        $redirect = 'index.php';
    }

    if ($login === '' || $password === '') {
        $error = 'Введите логин и пароль';
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id, password, fio FROM users WHERE login = ?');
        mysqli_stmt_bind_param($stmt, 's', $login);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_login'] = $login;
            $_SESSION['user_fio'] = $user['fio'];
            header('Location: ' . $redirect);
            exit;
        }
        $error = 'Неверный логин или пароль';
    }
}

$page_title = 'Вход — Учусь.РФ';
$active_nav = 'login';
require_once 'includes/header.php';
?>

<div class="wrapper narrow">
    <div class="box box-form">
        <h2>Вход в систему</h2>
        <?php if (!empty($success)): ?><div class="msg-ok"><?= h($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="msg-err"><?= h($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="redirect" value="<?= h($redirect) ?>">
            <div class="form-row"><label>Логин</label><input type="text" name="login"></div>
            <div class="form-row"><label>Пароль</label><input type="password" name="password"></div>
            <button type="submit" class="btn btn-cta">Войти</button>
        </form>
        <p class="link-nav"><a href="register.php">Еще не зарегистрированы? Регистрация</a></p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
