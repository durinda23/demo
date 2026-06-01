<?php
require_once 'config.php';
$page_title = 'Главная — Учусь.РФ';
$active_nav = 'home';
require_once 'includes/header.php';
?>

<div class="wrapper">
    <section class="hero">
        <div class="hero-text">
            <h1>Онлайн-курсы для профессионалов</h1>
            <p>Повышение квалификации, переподготовка и охрана труда — запишитесь на обучение за несколько минут.</p>
            <?php if (is_logged_in()): ?>
                <a href="application.php" class="btn btn-cta">Подать заявку на курс</a>
            <?php else: ?>
                <a href="login.php?redirect=application.php" class="btn btn-cta">Подать заявку на курс</a>
                <p class="hero-hint">Для подачи заявки нужна <a href="register.php">регистрация</a> или <a href="login.php">вход</a></p>
            <?php endif; ?>
        </div>
        <div class="hero-banner ad-placeholder">Рекламный баннер 320×200</div>
    </section>

    <div class="slider">
        <div class="slider-track">
            <div class="slide"><div class="slide-placeholder">Курсы повышения квалификации</div></div>
            <div class="slide"><div class="slide-placeholder">Переподготовка специалистов</div></div>
            <div class="slide"><div class="slide-placeholder">Охрана труда и безопасность</div></div>
            <div class="slide"><div class="slide-placeholder">Учитесь из любой точки России</div></div>
        </div>
        <button type="button" class="slider-btn prev">‹</button>
        <button type="button" class="slider-btn next">›</button>
    </div>
    <div class="slider-dots"><span class="active"></span><span></span><span></span><span></span></div>

    <section class="ads-row">
        <article class="ad-card">
            <div class="ad-placeholder tall">Реклама 1</div>
            <h3>Скидка 15% на первый курс</h3>
            <p>Действует до конца месяца для новых слушателей.</p>
        </article>
        <article class="ad-card ad-card-featured">
            <div class="ad-placeholder tall">Реклама 2</div>
            <h3>Корпоративное обучение</h3>
            <p>Оформите заявку для группы сотрудников вашей организации.</p>
            <a href="<?= is_logged_in() ? 'application.php' : 'login.php?redirect=application.php' ?>" class="btn btn-sm">Узнать больше</a>
        </article>
        <article class="ad-card">
            <div class="ad-placeholder tall">Реклама 3</div>
            <h3>Бесплатная консультация</h3>
            <p>Поможем выбрать программу под вашу специальность.</p>
        </article>
    </section>

    <section class="box features">
        <h2>Почему Учусь.РФ</h2>
        <div class="features-grid">
            <div class="feature-item">
                <strong>📚</strong>
                <p>Широкий каталог курсов</p>
            </div>
            <div class="feature-item">
                <strong>🕐</strong>
                <p>Удобная дата старта</p>
            </div>
            <div class="feature-item">
                <strong>💳</strong>
                <p>Несколько способов оплаты</p>
            </div>
            <div class="feature-item">
                <strong>✅</strong>
                <p>Отслеживание статуса заявки</p>
            </div>
        </div>
        <div class="cta-block">
            <h2>Готовы начать обучение?</h2>
            <p>Заполните форму заявки — администратор свяжется с вами после проверки.</p>
            <a href="<?= is_logged_in() ? 'application.php' : 'login.php?redirect=application.php' ?>" class="btn btn-cta">Перейти к форме заявки</a>
        </div>
    </section>
</div>

<script src="js/slider.js"></script>
<?php require_once 'includes/footer.php'; ?>
