<?php
$filter_status = $_GET['status'] ?? '';
$filter_user = trim($_GET['user'] ?? '');
$sort = $_GET['sort'] ?? 'id';
$order = ($_GET['order'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 8;

$allowed_sort = ['id', 'created_at', 'status', 'start_date'];
if (!in_array($sort, $allowed_sort, true)) {
    $sort = 'id';
}

$where = ['1=1'];
$params = [];
$types = '';

if ($filter_status !== '') {
    $where[] = 'a.status = ?';
    $params[] = $filter_status;
    $types .= 's';
}
if ($filter_user !== '') {
    $where[] = '(u.login LIKE ? OR u.fio LIKE ?)';
    $like = '%' . $filter_user . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

$where_sql = implode(' AND ', $where);

$count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM applications a JOIN users u ON u.id = a.user_id WHERE $where_sql");
if ($types) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['cnt'];
mysqli_stmt_close($count_stmt);

$total_pages = max(1, (int)ceil($total / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$list_sql = "
    SELECT a.id, a.start_date, a.payment_method, a.status, a.created_at,
           u.login, u.fio, u.phone, c.name AS course_name
    FROM applications a
    JOIN users u ON u.id = a.user_id
    JOIN courses c ON c.id = a.course_id
    WHERE $where_sql
    ORDER BY a.$sort $order
    LIMIT ? OFFSET ?
";
$list_stmt = mysqli_prepare($conn, $list_sql);
$params_list = array_merge($params, [$per_page, $offset]);
mysqli_stmt_bind_param($list_stmt, $types . 'ii', ...$params_list);
mysqli_stmt_execute($list_stmt);
$list = mysqli_stmt_get_result($list_stmt);

function app_url($overrides = []) {
    global $tab;
    $q = array_merge($_GET, ['tab' => 'applications'], $overrides);
    return 'admin.php?' . http_build_query($q);
}
?>
<h2>Модерация заявок (<?= $total ?>)</h2>

<form method="get" class="admin-filters">
    <input type="hidden" name="tab" value="applications">
    <div class="form-row">
        <label>Статус</label>
        <select name="status">
            <option value="">Все</option>
            <option value="Новая" <?= $filter_status === 'Новая' ? 'selected' : '' ?>>Новая</option>
            <option value="Идет обучение" <?= $filter_status === 'Идет обучение' ? 'selected' : '' ?>>Идет обучение</option>
            <option value="Обучение завершено" <?= $filter_status === 'Обучение завершено' ? 'selected' : '' ?>>Обучение завершено</option>
        </select>
    </div>
    <div class="form-row">
        <label>Поиск</label>
        <input type="text" name="user" value="<?= h($filter_user) ?>" placeholder="логин / ФИО">
    </div>
    <div class="form-row">
        <label>Сортировка</label>
        <select name="sort">
            <option value="id" <?= $sort === 'id' ? 'selected' : '' ?>>№</option>
            <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Дата подачи</option>
            <option value="status" <?= $sort === 'status' ? 'selected' : '' ?>>Статус</option>
        </select>
    </div>
    <div class="form-row">
        <label>Порядок</label>
        <select name="order">
            <option value="desc">Убыв.</option>
            <option value="asc" <?= ($_GET['order'] ?? '') === 'asc' ? 'selected' : '' ?>>Возр.</option>
        </select>
    </div>
    <button type="submit" class="btn">Фильтр</button>
</form>

<table class="data-table">
    <thead>
        <tr>
            <th>№</th>
            <th>Пользователь</th>
            <th>Курс</th>
            <th>Начало</th>
            <th>Оплата</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
    <?php if (mysqli_num_rows($list) === 0): ?>
        <tr><td colspan="7">Нет заявок</td></tr>
    <?php else: while ($row = mysqli_fetch_assoc($list)): ?>
        <tr>
            <td><?= (int)$row['id'] ?></td>
            <td><?= h($row['fio']) ?><br><small><?= h($row['login']) ?></small></td>
            <td><?= h($row['course_name']) ?></td>
            <td><?= date('d.m.Y', strtotime($row['start_date'])) ?></td>
            <td><?= h($row['payment_method']) ?></td>
            <td><span class="badge badge-<?= status_class($row['status']) ?>"><?= h($row['status']) ?></span></td>
            <td class="actions-cell">
                <form method="post" class="inline-form">
                    <input type="hidden" name="action" value="change_status">
                    <input type="hidden" name="app_id" value="<?= (int)$row['id'] ?>">
                    <select name="status">
                        <option value="Новая" <?= $row['status'] === 'Новая' ? 'selected' : '' ?>>Новая</option>
                        <option value="Идет обучение" <?= $row['status'] === 'Идет обучение' ? 'selected' : '' ?>>Идет обучение</option>
                        <option value="Обучение завершено" <?= $row['status'] === 'Обучение завершено' ? 'selected' : '' ?>>Завершено</option>
                    </select>
                    <button type="submit" class="btn btn-sm">OK</button>
                </form>
                <form method="post" class="inline-form" onsubmit="return confirm('Удалить заявку?');">
                    <input type="hidden" name="action" value="delete_app">
                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                </form>
            </td>
        </tr>
    <?php endwhile; endif; ?>
    </tbody>
</table>

<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
        <?php if ($p === $page): ?>
            <span class="current"><?= $p ?></span>
        <?php else: ?>
            <a href="<?= h(app_url(['page' => $p])) ?>"><?= $p ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?php endif; ?>
