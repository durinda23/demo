<?php
$courses = mysqli_query($conn, 'SELECT * FROM courses ORDER BY id');
$types_list = [
    'Курсы повышения квалификации',
    'Курсы переподготовки',
    'Курсы по охране труда',
];
?>
<h2>Модерация курсов</h2>

<div class="box-inner">
    <h3>Добавить курс</h3>
    <form method="post" class="admin-filters">
        <input type="hidden" name="action" value="add_course">
        <div class="form-row">
            <label>Название</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-row">
            <label>Тип</label>
            <select name="course_type" required>
                <?php foreach ($types_list as $t): ?>
                    <option value="<?= h($t) ?>"><?= h($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn">Добавить</button>
    </form>
</div>

<table class="data-table" style="margin-top:20px">
    <thead>
        <tr><th>ID</th><th>Курс (название и тип)</th><th>Действия</th></tr>
    </thead>
    <tbody>
    <?php while ($c = mysqli_fetch_assoc($courses)): ?>
        <tr>
            <td><?= (int)$c['id'] ?></td>
            <td>
                <form method="post" class="stack-form">
                    <input type="hidden" name="action" value="update_course">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <input type="text" name="name" value="<?= h($c['name']) ?>">
                    <select name="course_type">
                        <?php foreach ($types_list as $t): ?>
                            <option value="<?= h($t) ?>" <?= $c['course_type'] === $t ? 'selected' : '' ?>><?= h($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm">Сохранить</button>
                </form>
            </td>
            <td>
                <form method="post" onsubmit="return confirm('Удалить курс?');">
                    <input type="hidden" name="action" value="delete_course">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
