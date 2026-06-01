<?php require_once 'config.php'; head('Главная'); ?>

<div class="box">
    <h1>Портал онлайн-курсов</h1>
    <p>Повышение квалификации, переподготовка, охрана труда.</p>
    <p style="margin-top:12px">
        <?php if (is_logged_in()): ?>
            <a href="application.php" class="btn">Подать заявку на курс</a>
        <?php else: ?>
            <a href="login.php" class="btn">Войти и подать заявку</a>
        <?php endif; ?>
    </p>
</div>

<div class="ads">
    <div class="ad"><div class="ad-ph">Реклама 1</div><p>Скидка на первый курс</p></div>
    <div class="ad"><div class="ad-ph">Реклама 2</div><p>Корпоративное обучение</p></div>
    <div class="ad"><div class="ad-ph">Реклама 3</div><p>Консультация бесплатно</p></div>
</div>

<div class="box" style="text-align:center">
    <h2>Запись на курс</h2>
    <p>Заполните форму заявки — администратор проверит данные.</p>
    <a href="<?= is_logged_in() ? 'application.php' : 'login.php' ?>" class="btn">Перейти к форме заявки</a>
</div>

<?php foot(); ?>
