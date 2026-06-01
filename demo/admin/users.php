<?php
$users = mysqli_query($conn, 'SELECT id, login, fio, phone, email, created_at FROM users ORDER BY id DESC');
?>
<h2>Модерация пользователей</h2>
<p class="muted">Редактирование контактов и удаление учётных записей.</p>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Логин</th>
            <th>ФИО / телефон / e-mail</th>
            <th>Регистрация</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php while ($u = mysqli_fetch_assoc($users)): ?>
        <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= h($u['login']) ?></td>
            <td>
                <form method="post" class="stack-form">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                    <input type="text" name="fio" value="<?= h($u['fio']) ?>" placeholder="ФИО">
                    <input type="text" name="phone" value="<?= h($u['phone']) ?>" placeholder="Телефон">
                    <input type="email" name="email" value="<?= h($u['email']) ?>" placeholder="E-mail">
                    <button type="submit" class="btn btn-sm">Сохранить</button>
                </form>
            </td>
            <td><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Удалить пользователя и все его заявки?');">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
