<?php
include "config/db.php";
include "includes/header.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $plain_password = "";

    foreach ($_POST as $key => $value) {

        if (strpos($key, "password_real_") === 0) {
            $plain_password = $value;
            break;
        }
    }

    /*
    МИНИМУМ 8 СИМВОЛОВ
    */

    if (strlen($plain_password) < 8) {

        $error = "
        Пароль должен содержать минимум 8 символов.
        ";
    }

    /*
    ПРОВЕРКА:
    маленькие буквы,
    большие буквы,
    цифры,
    специальные символы
    */

    elseif (
        !preg_match('/[a-z]/', $plain_password) ||
        !preg_match('/[A-Z]/', $plain_password) ||
        !preg_match('/[0-9]/', $plain_password) ||
        !preg_match('/[\W_]/', $plain_password)
    ) {

        $error = "
        Пароль должен содержать:
        <br><br>

        • маленькие буквы
        <br>

        • большие буквы
        <br>

        • цифры
        <br>

        • специальные символы
        <br>

        Пример:
        <br>

        Worldus123!
        ";
    }

    /*
    ЕСЛИ ОШИБОК НЕТ
    */

    if (empty($error)) {

        $password = password_hash($plain_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users (email, password)
            VALUES (?, ?)
        ");

        $stmt->bind_param("ss", $email, $password);

        if ($stmt->execute()) {

            header("Location: login.php");
            exit();

        } else {

            $error = "Пользователь уже существует.";
        }
    }
}
?>

<div class="container">
    <div class="card">
        <h2>Регистрация</h2>

        <form method="POST" autocomplete="off">

            <input 
                type="email"
                name="fake_email"
                style="display:none"
            >

            <input 
                type="password"
                name="fake_password"
                style="display:none"
            >

            <input 
                type="email"
                name="email"
                placeholder="Email"
                autocomplete="new-password"
                autocorrect="off"
                autocapitalize="off"
                spellcheck="false"
                required
            >

            <br><br>

            <input 
                type="text"
                name="hidden_password"
                style="display:none"
                autocomplete="password"
            >

            <input 
                type="password"
                name="password_real_<?= rand(1000,9999) ?>"
                placeholder="Пароль"
                minlength="8"
                autocomplete="new-password"
                autocorrect="off"
                autocapitalize="off"
                spellcheck="false"
                data-lpignore="true"
                data-form-type="other"
                required
            >

            <?php if (!empty($error)): ?>

                <div class="register-error">
                    <?= $error ?>
                </div>

            <?php endif; ?>

            <br><br>

            <button class="btn green">
                Зарегистрироваться
            </button>

        </form>
    </div>
</div>

<?php include "includes/footer.php"; ?>