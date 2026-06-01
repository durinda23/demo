<?php
require_once 'config.php';
require_login();

$uid = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_text'], $_POST['app_id'])) {
    $app_id = (int)$_POST['app_id'];
    $text = trim($_POST['review_text']);
    $q = mysqli_query($conn, "SELECT a.id FROM applications a
        LEFT JOIN reviews r ON r.application_id=a.id
        WHERE a.id=$app_id AND a.user_id=$uid AND a.status!='Новая' AND r.id IS NULL");
    if (mysqli_fetch_assoc($q) && $text !== '') {
        $stmt = mysqli_prepare($conn, 'INSERT INTO reviews (application_id,user_id,review_text) VALUES (?,?,?)');
        mysqli_stmt_bind_param($stmt, 'iis', $app_id, $uid, $text);
        mysqli_stmt_execute($stmt);
    }
}

$apps = mysqli_query($conn, "SELECT a.*, c.name AS course_name, r.review_text
    FROM applications a
    JOIN courses c ON c.id=a.course_id
    LEFT JOIN reviews r ON r.application_id=a.id
    WHERE a.user_id=$uid ORDER BY a.id DESC");

head('Личный кабинет');
?>
<?php if (!empty($_GET['ok'])): ?><div class="msg-ok">Заявка отправлена администратору</div><?php endif; ?>

<div class="slider" id="slider">
    <div class="slider-track" id="track">
        <div class="slide">Слайд 1</div>
        <div class="slide">Слайд 2</div>
        <div class="slide">Слайд 3</div>
        <div class="slide">Слайд 4</div>
    </div>
    <button type="button" class="slider-btn prev" onclick="slide(-1)">‹</button>
    <button type="button" class="slider-btn next" onclick="slide(1)">›</button>
</div>

<div class="box">
    <h2>Мои заявки</h2>
    <p><a href="application.php" class="btn">Новая заявка</a></p>
    <table>
        <tr><th>№</th><th>Курс</th><th>Начало</th><th>Оплата</th><th>Статус</th><th>Отзыв</th></tr>
        <?php while ($row = mysqli_fetch_assoc($apps)): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= h($row['course_name']) ?></td>
            <td><?= date('d.m.Y', strtotime($row['start_date'])) ?></td>
            <td><?= h($row['payment_method']) ?></td>
            <td><?= h($row['status']) ?></td>
            <td>
                <?php if ($row['review_text']): ?>
                    <?= h($row['review_text']) ?>
                <?php elseif ($row['status'] !== 'Новая'): ?>
                    <form method="post">
                        <input type="hidden" name="app_id" value="<?= $row['id'] ?>">
                        <textarea name="review_text" rows="2" required></textarea>
                        <button class="btn">Отправить</button>
                    </form>
                <?php else: ?>
                    <i>после смены статуса</i>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<script>
var n = 0;
function slide(d) {
    var slides = document.querySelectorAll('#track .slide').length;
    n = (n + d + slides) % slides;
    document.getElementById('track').style.transform = 'translateX(-' + (n * 100) + '%)';
}
setInterval(function(){ slide(1); }, 3000);
</script>
<?php foot(); ?>
