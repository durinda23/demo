<?php
require_once 'config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = mysqli_prepare($conn, 'SELECT id, password, fio FROM users WHERE login=?');
    mysqli_stmt_bind_param($stmt, 's', $login);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_fio'] = $user['fio'];
        header('Location: cabinet.php');
        exit;
    }
    $error = 'Неверный логин или пароль';
}

head('Вход');
?>
<div class="box">
    <h2>Вход</h2>
    <?php if (!empty($_GET['ok'])): ?><div class="msg-ok">Регистрация успешна. Войдите.</div><?php endif; ?>
    <?php if ($error): ?><div class="msg-err"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-row"><label>Логин</label><input name="login"></div>
        <div class="form-row"><label>Пароль</label><input type="password" name="password"></div>
        <button class="btn">Войти</button>
    </form>
    <p style="margin-top:10px"><a href="register.php">Еще не зарегистрированы? Регистрация</a></p>
</div>
<?php foot(); ?>
