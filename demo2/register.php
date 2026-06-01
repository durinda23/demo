<?php
require_once 'config.php';
$errors = [];
$login = $fio = $phone = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $fio = trim($_POST['fio'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
        $errors['login'] = 'Логин: латиница и цифры, от 6 символов';
    } else {
        $r = mysqli_query($conn, "SELECT id FROM users WHERE login='" . mysqli_real_escape_string($conn, $login) . "'");
        if (mysqli_fetch_assoc($r)) $errors['login'] = 'Логин занят';
    }
    if (strlen($password) < 8) $errors['password'] = 'Пароль от 8 символов';
    if ($fio === '') $errors['fio'] = 'Введите ФИО';
    if ($phone === '') $errors['phone'] = 'Введите телефон';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Некорректный e-mail';

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, 'INSERT INTO users (login,password,fio,phone,email) VALUES (?,?,?,?,?)');
        mysqli_stmt_bind_param($stmt, 'sssss', $login, $hash, $fio, $phone, $email);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: login.php?ok=1');
            exit;
        }
        $errors['general'] = 'Ошибка сохранения';
    }
}

head('Регистрация');
?>
<div class="box">
    <h2>Регистрация</h2>
    <?php if (!empty($errors['general'])): ?><div class="msg-err"><?= h($errors['general']) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-row"><label>Логин</label><input name="login" value="<?= h($login) ?>">
        <?php if (!empty($errors['login'])): ?><div class="error"><?= h($errors['login']) ?></div><?php endif; ?></div>
        <div class="form-row"><label>Пароль</label><input type="password" name="password">
        <?php if (!empty($errors['password'])): ?><div class="error"><?= h($errors['password']) ?></div><?php endif; ?></div>
        <div class="form-row"><label>ФИО</label><input name="fio" value="<?= h($fio) ?>">
        <?php if (!empty($errors['fio'])): ?><div class="error"><?= h($errors['fio']) ?></div><?php endif; ?></div>
        <div class="form-row"><label>Телефон</label><input name="phone" value="<?= h($phone) ?>">
        <?php if (!empty($errors['phone'])): ?><div class="error"><?= h($errors['phone']) ?></div><?php endif; ?></div>
        <div class="form-row"><label>E-mail</label><input name="email" value="<?= h($email) ?>">
        <?php if (!empty($errors['email'])): ?><div class="error"><?= h($errors['email']) ?></div><?php endif; ?></div>
        <button class="btn">Зарегистрироваться</button>
    </form>
    <p style="margin-top:10px"><a href="login.php">Уже есть аккаунт? Вход</a></p>
</div>
<?php foot(); ?>
