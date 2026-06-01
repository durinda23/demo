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

    if ($login === '') {
        $errors['login'] = 'Введите логин';
    } elseif (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
        $errors['login'] = 'Логин: только латиница и цифры, минимум 6 символов';
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE login = ?');
        mysqli_stmt_bind_param($stmt, 's', $login);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors['login'] = 'Такой логин уже занят';
        }
        mysqli_stmt_close($stmt);
    }

    if ($password === '') {
        $errors['password'] = 'Введите пароль';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Пароль должен быть не менее 8 символов';
    }

    if ($fio === '') {
        $errors['fio'] = 'Введите ФИО';
    }
    if ($phone === '') {
        $errors['phone'] = 'Введите телефон';
    }
    if ($email === '') {
        $errors['email'] = 'Введите e-mail';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный e-mail';
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, 'INSERT INTO users (login, password, fio, phone, email) VALUES (?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sssss', $login, $hash, $fio, $phone, $email);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: login.php?registered=1');
            exit;
        }
        $errors['general'] = 'Ошибка сохранения в БД';
        mysqli_stmt_close($stmt);
    }
}
$page_title = 'Регистрация — Учусь.РФ';
$active_nav = 'register';
require_once 'includes/header.php';
?>

<div class="wrapper narrow">
    <div class="box box-form">
        <h2>Регистрация</h2>
        <?php if (!empty($errors['general'])): ?>
            <div class="msg-err"><?= h($errors['general']) ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-row">
                <label>Логин</label>
                <input type="text" name="login" value="<?= h($login) ?>">
                <?php if (!empty($errors['login'])): ?><div class="error"><?= h($errors['login']) ?></div><?php endif; ?>
            </div>
            <div class="form-row">
                <label>Пароль</label>
                <input type="password" name="password">
                <?php if (!empty($errors['password'])): ?><div class="error"><?= h($errors['password']) ?></div><?php endif; ?>
            </div>
            <div class="form-row">
                <label>ФИО</label>
                <input type="text" name="fio" value="<?= h($fio) ?>">
                <?php if (!empty($errors['fio'])): ?><div class="error"><?= h($errors['fio']) ?></div><?php endif; ?>
            </div>
            <div class="form-row">
                <label>Телефон</label>
                <input type="text" name="phone" value="<?= h($phone) ?>">
                <?php if (!empty($errors['phone'])): ?><div class="error"><?= h($errors['phone']) ?></div><?php endif; ?>
            </div>
            <div class="form-row">
                <label>E-mail</label>
                <input type="email" name="email" value="<?= h($email) ?>">
                <?php if (!empty($errors['email'])): ?><div class="error"><?= h($errors['email']) ?></div><?php endif; ?>
            </div>
            <button type="submit" class="btn btn-cta">Зарегистрироваться</button>
        </form>
        <p class="link-nav"><a href="login.php">Уже зарегистрированы? Вход</a></p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
