<?php include "includes/header.php"; ?>

<div class="container">
    <div class="card">
        <h1>Добро пожаловать в Worldus!</h1>
        

        <p>
            Обучайтесь через квесты, проходите уровни и прокачивайте знания.
        </p>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="buttons">
                <a href="register.php" class="btn green">Регистрация</a>
                <a href="login.php" class="btn blue">Войти</a>
            </div>
        <?php else: ?>
            <div class="buttons">
                <a href="categories.php" class="btn blue">Начать</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>