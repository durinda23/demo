<?php
require_once 'config.php';

define('ADMIN_LOGIN', 'Admin26');
define('ADMIN_PASS', 'Demo20');

if (isset($_GET['logout'])) {
    unset($_SESSION['admin']);
    header('Location: admin.php');
    exit;
}

$admin_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    if ($_POST['admin_login'] === ADMIN_LOGIN && $_POST['admin_pass'] === ADMIN_PASS) {
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    }
    $admin_error = 'Неверный логин или пароль администратора';
}

if (empty($_SESSION['admin'])):
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ — Учусь.РФ</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="admin-body">
<div class="wrapper narrow" style="padding-top:60px">
    <div class="box box-form">
        <h2>Вход администратора</h2>
        <?php if ($admin_error): ?><div class="msg-err"><?= h($admin_error) ?></div><?php endif; ?>
        <form method="post">
            <div class="form-row"><label>Логин</label><input type="text" name="admin_login"></div>
            <div class="form-row"><label>Пароль</label><input type="password" name="admin_pass"></div>
            <button type="submit" class="btn btn-cta">Войти</button>
        </form>
        <p class="link-nav"><a href="index.php">На сайт</a></p>
    </div>
</div>
</body>
</html>
<?php
exit;
endif;

$tab = $_GET['tab'] ?? 'applications';
$allowed_tabs = ['applications', 'users', 'courses', 'reviews'];
if (!in_array($tab, $allowed_tabs, true)) {
    $tab = 'applications';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_status') {
        $app_id = (int)$_POST['app_id'];
        $status = $_POST['status'] ?? '';
        $allowed = ['Новая', 'Идет обучение', 'Обучение завершено'];
        if (in_array($status, $allowed, true)) {
            $stmt = mysqli_prepare($conn, 'UPDATE applications SET status = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'si', $status, $app_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['admin_popup'] = 'Статус заявки №' . $app_id . ' изменён';
        }
    }

    if ($action === 'delete_app') {
        $id = (int)$_POST['id'];
        mysqli_query($conn, 'DELETE FROM applications WHERE id = ' . $id);
        $_SESSION['admin_popup'] = 'Заявка №' . $id . ' удалена';
    }

    if ($action === 'delete_user') {
        $id = (int)$_POST['id'];
        mysqli_query($conn, 'DELETE FROM users WHERE id = ' . $id);
        $_SESSION['admin_popup'] = 'Пользователь удалён';
    }

    if ($action === 'update_user') {
        $id = (int)$_POST['id'];
        $fio = trim($_POST['fio'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $stmt = mysqli_prepare($conn, 'UPDATE users SET fio=?, phone=?, email=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'sssi', $fio, $phone, $email, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['admin_popup'] = 'Данные пользователя обновлены';
    }

    if ($action === 'add_course') {
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['course_type'] ?? '');
        if ($name && $type) {
            $stmt = mysqli_prepare($conn, 'INSERT INTO courses (name, course_type) VALUES (?, ?)');
            mysqli_stmt_bind_param($stmt, 'ss', $name, $type);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['admin_popup'] = 'Курс добавлен';
        }
    }

    if ($action === 'update_course') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['course_type'] ?? '');
        $stmt = mysqli_prepare($conn, 'UPDATE courses SET name=?, course_type=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'ssi', $name, $type, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['admin_popup'] = 'Курс обновлён';
    }

    if ($action === 'delete_course') {
        $id = (int)$_POST['id'];
        mysqli_query($conn, 'DELETE FROM courses WHERE id = ' . $id);
        $_SESSION['admin_popup'] = 'Курс удалён';
    }

    if ($action === 'delete_review') {
        $id = (int)$_POST['id'];
        mysqli_query($conn, 'DELETE FROM reviews WHERE id = ' . $id);
        $_SESSION['admin_popup'] = 'Отзыв удалён';
    }

    $q = $_GET;
    $q['tab'] = $tab;
    header('Location: admin.php?' . http_build_query($q));
    exit;
}

$popup = $_SESSION['admin_popup'] ?? '';
unset($_SESSION['admin_popup']);

function admin_tab_url($t) {
    return 'admin.php?tab=' . urlencode($t);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель — Учусь.РФ</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="admin-body">
<div class="popup-overlay <?= $popup ? 'show' : '' ?>" id="overlay"></div>
<div class="popup <?= $popup ? 'show' : '' ?>" id="popup">
    <p><?= h($popup) ?></p>
    <button type="button" class="btn" onclick="document.getElementById('popup').classList.remove('show');document.getElementById('overlay').classList.remove('show');">OK</button>
</div>

<header class="site-header admin-header">
    <div class="wrapper header-inner">
        <span class="logo">Учусь<span>.РФ</span> — Админ</span>
        <nav class="main-nav">
            <a href="index.php">Сайт</a>
            <a href="admin.php?logout=1">Выход</a>
        </nav>
    </div>
</header>

<div class="wrapper">
    <nav class="admin-tabs">
        <a href="<?= admin_tab_url('applications') ?>" class="<?= $tab === 'applications' ? 'active' : '' ?>">Заявки</a>
        <a href="<?= admin_tab_url('users') ?>" class="<?= $tab === 'users' ? 'active' : '' ?>">Пользователи</a>
        <a href="<?= admin_tab_url('courses') ?>" class="<?= $tab === 'courses' ? 'active' : '' ?>">Курсы</a>
        <a href="<?= admin_tab_url('reviews') ?>" class="<?= $tab === 'reviews' ? 'active' : '' ?>">Отзывы</a>
    </nav>

    <div class="box">
        <?php if ($tab === 'applications'): ?>
            <?php include __DIR__ . '/admin/applications.php'; ?>
        <?php elseif ($tab === 'users'): ?>
            <?php include __DIR__ . '/admin/users.php'; ?>
        <?php elseif ($tab === 'courses'): ?>
            <?php include __DIR__ . '/admin/courses.php'; ?>
        <?php else: ?>
            <?php include __DIR__ . '/admin/reviews.php'; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
