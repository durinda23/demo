<?php
require_once 'config.php';
require_login('cabinet.php');

$uid = (int)$_SESSION['user_id'];
$review_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_app_id'])) {
    $app_id = (int)$_POST['review_app_id'];
    $text = trim($_POST['review_text'] ?? '');

    $check = mysqli_prepare($conn, '
        SELECT a.id FROM applications a
        LEFT JOIN reviews r ON r.application_id = a.id
        WHERE a.id = ? AND a.user_id = ? AND a.status != "Новая" AND r.id IS NULL
    ');
    mysqli_stmt_bind_param($check, 'ii', $app_id, $uid);
    mysqli_stmt_execute($check);
    $can = mysqli_stmt_get_result($check);
    if (mysqli_fetch_assoc($can) && $text !== '') {
        $ins = mysqli_prepare($conn, 'INSERT INTO reviews (application_id, user_id, review_text) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($ins, 'iis', $app_id, $uid, $text);
        mysqli_stmt_execute($ins);
        mysqli_stmt_close($ins);
        $review_msg = 'Отзыв сохранен';
    } else {
        $review_msg = 'Нельзя оставить отзыв для этой заявки';
    }
    mysqli_stmt_close($check);
}

$sql = '
    SELECT a.id, a.start_date, a.payment_method, a.status, a.created_at,
           c.name AS course_name, c.course_type, r.review_text
    FROM applications a
    JOIN courses c ON c.id = a.course_id
    LEFT JOIN reviews r ON r.application_id = a.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
';
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$apps = mysqli_stmt_get_result($stmt);

$page_title = 'Личный кабинет — Учусь.РФ';
$active_nav = 'cabinet';
require_once 'includes/header.php';
?>

<div class="wrapper">
    <div class="page-head">
        <h1>Личный кабинет</h1>
        <a href="application.php" class="btn btn-cta">Подать новую заявку</a>
    </div>

    <?php if (!empty($_GET['created'])): ?>
        <div class="msg-ok">Заявка отправлена на согласование администратору</div>
    <?php endif; ?>
    <?php if ($review_msg): ?>
        <div class="msg-ok"><?= h($review_msg) ?></div>
    <?php endif; ?>

    <div class="box">
        <h2>Мои заявки</h2>
        <?php if (mysqli_num_rows($apps) === 0): ?>
            <p class="empty-text">У вас пока нет заявок. <a href="application.php">Оформите первую заявку</a>.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Курс</th>
                        <th>Тип</th>
                        <th>Начало</th>
                        <th>Оплата</th>
                        <th>Статус</th>
                        <th>Подана</th>
                        <th>Отзыв</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($apps)): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?= h($row['course_name']) ?></td>
                        <td><?= h($row['course_type']) ?></td>
                        <td><?= date('d.m.Y', strtotime($row['start_date'])) ?></td>
                        <td><?= h($row['payment_method']) ?></td>
                        <td><span class="badge badge-<?= status_class($row['status']) ?>"><?= h($row['status']) ?></span></td>
                        <td><?= date('d.m.Y H:i', strtotime($row['created_at'])) ?></td>
                        <td>
                            <?php if ($row['review_text']): ?>
                                <?= h($row['review_text']) ?>
                            <?php elseif ($row['status'] !== 'Новая'): ?>
                                <form method="post" class="review-form">
                                    <input type="hidden" name="review_app_id" value="<?= (int)$row['id'] ?>">
                                    <textarea name="review_text" rows="2" placeholder="Ваш отзыв" required></textarea>
                                    <button type="submit" class="btn btn-sm">Отправить</button>
                                </form>
                            <?php else: ?>
                                <em class="muted">После смены статуса</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
