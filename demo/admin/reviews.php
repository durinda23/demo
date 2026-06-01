<?php
$reviews = mysqli_query($conn, "
    SELECT r.id, r.review_text, r.created_at,
           u.fio, u.login, c.name AS course_name, a.id AS app_id
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    JOIN applications a ON a.id = r.application_id
    JOIN courses c ON c.id = a.course_id
    ORDER BY r.created_at DESC
");
?>
<h2>Модерация отзывов</h2>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Заявка / курс</th>
            <th>Отзыв</th>
            <th>Дата</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (mysqli_num_rows($reviews) === 0): ?>
        <tr><td colspan="6">Отзывов пока нет</td></tr>
    <?php else: while ($r = mysqli_fetch_assoc($reviews)): ?>
        <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= h($r['fio']) ?><br><small><?= h($r['login']) ?></small></td>
            <td>№<?= (int)$r['app_id'] ?><br><?= h($r['course_name']) ?></td>
            <td><?= h($r['review_text']) ?></td>
            <td><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Удалить отзыв?');">
                    <input type="hidden" name="action" value="delete_review">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                </form>
            </td>
        </tr>
    <?php endwhile; endif; ?>
    </tbody>
</table>
