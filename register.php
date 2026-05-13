<?php

require_once __DIR__ . '/vendor/autoload.php';
include "config/db.php";
include "includes/header.php";

use Worldus\AuthService;
use Worldus\MysqliDatabase;

$db = new MysqliDatabase($conn);
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

    $result = AuthService::register($db, $email, $plain_password);
    if ($result === true) {
        header("Location: login.php");
        exit();
    }

    $error = $result;
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