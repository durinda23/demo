<?php
require_once 'config.php';
require_login();

$errors = [];
$courses = mysqli_query($conn, 'SELECT * FROM courses ORDER BY name');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int)($_POST['course_id'] ?? 0);
    $date = trim($_POST['start_date'] ?? '');
    $pay = trim($_POST['payment_method'] ?? '');

    if ($course_id < 1) $errors['course'] = 'Выберите курс';
    if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
        $errors['date'] = 'Дата в формате ДД.ММ.ГГГГ';
    } else {
        $p = explode('.', $date);
        if (!checkdate((int)$p[1], (int)$p[0], (int)$p[2])) $errors['date'] = 'Неверная дата';
        else $date_db = $p[2] . '-' . $p[1] . '-' . $p[0];
    }
    if ($pay === '') $errors['pay'] = 'Выберите оплату';

    if (!$errors) {
        $uid = (int)$_SESSION['user_id'];
        $stmt = mysqli_prepare($conn, 'INSERT INTO applications (user_id,course_id,start_date,payment_method) VALUES (?,?,?,?)');
        mysqli_stmt_bind_param($stmt, 'iiss', $uid, $course_id, $date_db, $pay);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: cabinet.php?ok=1');
            exit;
        }
        $errors['general'] = 'Ошибка записи';
    }
}

head('Заявка на курс');
?>
<div class="box">
    <h2>Оформление заявки</h2>
    <?php if (!empty($errors['general'])): ?><div class="msg-err"><?= h($errors['general']) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-row"><label>Курс</label>
            <select name="course_id">
                <option value="">— выберите —</option>
                <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                    <option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <?php if (!empty($errors['course'])): ?><div class="error"><?= h($errors['course']) ?></div><?php endif; ?>
        </div>
        <div class="form-row"><label>Дата начала (ДД.ММ.ГГГГ)</label>
            <input name="start_date" placeholder="01.09.2026">
            <?php if (!empty($errors['date'])): ?><div class="error"><?= h($errors['date']) ?></div><?php endif; ?>
        </div>
        <div class="form-row"><label>Оплата</label>
            <select name="payment_method">
                <option value="">—</option>
                <option>Банковская карта</option>
                <option>Счет для юр. лиц</option>
                <option>Наличные</option>
            </select>
            <?php if (!empty($errors['pay'])): ?><div class="error"><?= h($errors['pay']) ?></div><?php endif; ?>
        </div>
        <button class="btn">Отправить заявку</button>
    </form>
    <p style="margin-top:10px"><a href="cabinet.php">← Кабинет</a></p>
</div>
<?php foot(); ?>
