<?php
require_once 'config.php';
require_login('application.php');

$errors = [];
$courses = mysqli_query($conn, 'SELECT id, name, course_type FROM courses ORDER BY name');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int)($_POST['course_id'] ?? 0);
    $start_date_str = trim($_POST['start_date'] ?? '');
    $payment = trim($_POST['payment_method'] ?? '');

    if ($course_id <= 0) {
        $errors['course'] = 'Выберите курс';
    }
    if ($start_date_str === '') {
        $errors['start_date'] = 'Укажите дату начала';
    } elseif (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $start_date_str)) {
        $errors['start_date'] = 'Формат даты: ДД.ММ.ГГГГ';
    } else {
        $parts = explode('.', $start_date_str);
        $d = (int)$parts[0];
        $m = (int)$parts[1];
        $y = (int)$parts[2];
        if (!checkdate($m, $d, $y)) {
            $errors['start_date'] = 'Некорректная дата';
        } else {
            $start_date_db = sprintf('%04d-%02d-%02d', $y, $m, $d);
        }
    }
    if ($payment === '') {
        $errors['payment'] = 'Выберите способ оплаты';
    }

    if (empty($errors)) {
        $uid = (int)$_SESSION['user_id'];
        $stmt = mysqli_prepare($conn, 'INSERT INTO applications (user_id, course_id, start_date, payment_method) VALUES (?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'iiss', $uid, $course_id, $start_date_db, $payment);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: cabinet.php?created=1');
            exit;
        }
        $errors['general'] = 'Ошибка сохранения заявки';
        mysqli_stmt_close($stmt);
    }
}

$page_title = 'Подать заявку — Учусь.РФ';
$active_nav = 'application';
require_once 'includes/header.php';
?>

<div class="wrapper">
    <div class="page-head">
        <h1>Оформление заявки на курс</h1>
        <p>Выберите программу, дату начала и способ оплаты. Заявка уйдёт администратору на согласование.</p>
    </div>

    <div class="form-page">
        <div class="form-side">
            <div class="box box-form">
                <?php if (!empty($errors['general'])): ?>
                    <div class="msg-err"><?= h($errors['general']) ?></div>
                <?php endif; ?>
                <form method="post" action="">
                    <div class="form-row">
                        <label>Курс</label>
                        <select name="course_id">
                            <option value="">— выберите курс —</option>
                            <?php mysqli_data_seek($courses, 0); while ($c = mysqli_fetch_assoc($courses)): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= h($c['name']) ?> (<?= h($c['course_type']) ?>)</option>
                            <?php endwhile; ?>
                        </select>
                        <?php if (!empty($errors['course'])): ?><div class="error"><?= h($errors['course']) ?></div><?php endif; ?>
                    </div>
                    <div class="form-row">
                        <label>Дата начала (ДД.ММ.ГГГГ)</label>
                        <input type="text" name="start_date" placeholder="01.09.2026">
                        <?php if (!empty($errors['start_date'])): ?><div class="error"><?= h($errors['start_date']) ?></div><?php endif; ?>
                    </div>
                    <div class="form-row">
                        <label>Способ оплаты</label>
                        <select name="payment_method">
                            <option value="">— выберите —</option>
                            <option value="Банковская карта">Банковская карта</option>
                            <option value="Счет для юр. лиц">Счет для юр. лиц</option>
                            <option value="Наличные в офисе">Наличные в офисе</option>
                        </select>
                        <?php if (!empty($errors['payment'])): ?><div class="error"><?= h($errors['payment']) ?></div><?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-cta">Отправить заявку</button>
                </form>
            </div>
        </div>
        <aside class="form-aside">
            <div class="ad-placeholder side">Подсказка / реклама 280×400</div>
            <ul class="form-tips">
                <li>Статус заявки: «Новая»</li>
                <li>После одобрения можно оставить отзыв</li>
                <li>История — в личном кабинете</li>
            </ul>
            <a href="cabinet.php" class="btn btn-secondary">← Личный кабинет</a>
        </aside>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
