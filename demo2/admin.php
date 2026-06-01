<?php
require_once 'config.php';

define('ADMIN_LOGIN', 'Admin26');
define('ADMIN_PASS', 'Demo20');

if (isset($_GET['logout'])) {
    unset($_SESSION['admin']);
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    if ($_POST['admin_login'] === ADMIN_LOGIN && $_POST['admin_pass'] === ADMIN_PASS) {
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    }
    $admin_err = 'Неверный логин или пароль';
}

if (empty($_SESSION['admin'])) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><link rel="stylesheet" href="css/style.css"></head><body>';
    echo '<div class="wrap"><div class="box"><h2>Админ</h2>';
    if (!empty($admin_err)) echo '<div class="msg-err">', h($admin_err), '</div>';
    echo '<form method="post"><div class="form-row"><label>Логин</label><input name="admin_login"></div>';
    echo '<div class="form-row"><label>Пароль</label><input type="password" name="admin_pass"></div>';
    echo '<button class="btn">Войти</button></form><p><a href="index.php">На сайт</a></p></div></div></body></html>';
    exit;
}

$tab = $_GET['tab'] ?? 'apps';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['status'], $_POST['app_id'])) {
    $st = $_POST['status'];
    $id = (int)$_POST['app_id'];
    if (in_array($st, ['Новая', 'Идет обучение', 'Обучение завершено'], true)) {
      $stmt = mysqli_prepare($conn, 'UPDATE applications SET status=? WHERE id=?');
      mysqli_stmt_bind_param($stmt, 'si', $st, $id);
      mysqli_stmt_execute($stmt);
    }
  }
  if (isset($_POST['del_user'])) mysqli_query($conn, 'DELETE FROM users WHERE id=' . (int)$_POST['del_user']);
  if (isset($_POST['del_course'])) mysqli_query($conn, 'DELETE FROM courses WHERE id=' . (int)$_POST['del_course']);
  if (isset($_POST['del_review'])) mysqli_query($conn, 'DELETE FROM reviews WHERE id=' . (int)$_POST['del_review']);
  if (isset($_POST['new_course_name'], $_POST['new_course_type'])) {
    $n = trim($_POST['new_course_name']);
    $t = trim($_POST['new_course_type']);
    if ($n && $t) {
      $stmt = mysqli_prepare($conn, 'INSERT INTO courses (name,course_type) VALUES (?,?)');
      mysqli_stmt_bind_param($stmt, 'ss', $n, $t);
      mysqli_stmt_execute($stmt);
    }
  }
  header('Location: admin.php?tab=' . urlencode($tab));
  exit;
}

head('Админ');
$st_filter = $_GET['status'] ?? '';
?>
<p class="tabs">
    <a href="admin.php?tab=apps" class="<?= $tab === 'apps' ? 'active' : '' ?>">Заявки</a>
    <a href="admin.php?tab=users" class="<?= $tab === 'users' ? 'active' : '' ?>">Пользователи</a>
    <a href="admin.php?tab=courses" class="<?= $tab === 'courses' ? 'active' : '' ?>">Курсы</a>
    <a href="admin.php?tab=reviews" class="<?= $tab === 'reviews' ? 'active' : '' ?>">Отзывы</a>
    <a href="admin.php?logout=1">Выход</a>
</p>

<div class="box">
<?php if ($tab === 'apps'): ?>
    <h2>Заявки</h2>
    <form method="get">
        <input type="hidden" name="tab" value="apps">
        Статус: <select name="status" onchange="this.form.submit()">
            <option value="">Все</option>
            <option value="Новая" <?= $st_filter === 'Новая' ? 'selected' : '' ?>>Новая</option>
            <option value="Идет обучение" <?= $st_filter === 'Идет обучение' ? 'selected' : '' ?>>Идет обучение</option>
            <option value="Обучение завершено" <?= $st_filter === 'Обучение завершено' ? 'selected' : '' ?>>Завершено</option>
        </select>
    </form>
    <?php
    $sql = "SELECT a.*, u.fio, u.login, c.name AS course_name FROM applications a
        JOIN users u ON u.id=a.user_id JOIN courses c ON c.id=a.course_id";
    if ($st_filter !== '') $sql .= " WHERE a.status='" . mysqli_real_escape_string($conn, $st_filter) . "'";
    $sql .= ' ORDER BY a.id DESC';
    $list = mysqli_query($conn, $sql);
    ?>
    <table>
        <tr><th>№</th><th>Пользователь</th><th>Курс</th><th>Дата</th><th>Статус</th><th>Изменить</th></tr>
        <?php while ($r = mysqli_fetch_assoc($list)): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= h($r['fio']) ?> (<?= h($r['login']) ?>)</td>
            <td><?= h($r['course_name']) ?></td>
            <td><?= date('d.m.Y', strtotime($r['start_date'])) ?></td>
            <td><?= h($r['status']) ?></td>
            <td>
                <form method="post" style="display:inline">
                    <input type="hidden" name="app_id" value="<?= $r['id'] ?>">
                    <select name="status">
                        <option>Новая</option>
                        <option <?= $r['status'] === 'Идет обучение' ? 'selected' : '' ?>>Идет обучение</option>
                        <option <?= $r['status'] === 'Обучение завершено' ? 'selected' : '' ?>>Обучение завершено</option>
                    </select>
                    <button class="btn">OK</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

<?php elseif ($tab === 'users'): ?>
    <h2>Пользователи</h2>
    <table>
        <tr><th>ID</th><th>Логин</th><th>ФИО</th><th>Телефон</th><th>Email</th><th></th></tr>
        <?php $u = mysqli_query($conn, 'SELECT * FROM users ORDER BY id'); while ($r = mysqli_fetch_assoc($u)): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= h($r['login']) ?></td>
            <td><?= h($r['fio']) ?></td>
            <td><?= h($r['phone']) ?></td>
            <td><?= h($r['email']) ?></td>
            <td>
                <form method="post"><input type="hidden" name="del_user" value="<?= $r['id'] ?>">
                <button class="btn" onclick="return confirm('Удалить?')">Удалить</button></form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

<?php elseif ($tab === 'courses'): ?>
    <h2>Курсы</h2>
    <form method="post">
        <input name="new_course_name" placeholder="Название" required>
        <select name="new_course_type">
            <option>Курсы повышения квалификации</option>
            <option>Курсы переподготовки</option>
            <option>Курсы по охране труда</option>
        </select>
        <button class="btn">Добавить</button>
    </form>
    <table>
        <tr><th>ID</th><th>Название</th><th>Тип</th><th></th></tr>
        <?php $c = mysqli_query($conn, 'SELECT * FROM courses'); while ($r = mysqli_fetch_assoc($c)): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= h($r['name']) ?></td>
            <td><?= h($r['course_type']) ?></td>
            <td>
                <form method="post"><input type="hidden" name="del_course" value="<?= $r['id'] ?>">
                <button class="btn" onclick="return confirm('Удалить?')">Удалить</button></form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

<?php else: ?>
    <h2>Отзывы</h2>
    <table>
        <tr><th>ID</th><th>Пользователь</th><th>Текст</th><th></th></tr>
        <?php
        $rev = mysqli_query($conn, 'SELECT r.*, u.fio FROM reviews r JOIN users u ON u.id=r.user_id ORDER BY r.id DESC');
        while ($r = mysqli_fetch_assoc($rev)):
        ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= h($r['fio']) ?></td>
            <td><?= h($r['review_text']) ?></td>
            <td>
                <form method="post"><input type="hidden" name="del_review" value="<?= $r['id'] ?>">
                <button class="btn">Удалить</button></form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php endif; ?>
</div>
<?php foot(); ?>
