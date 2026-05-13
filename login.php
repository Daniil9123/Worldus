<?php

require_once __DIR__ . '/vendor/autoload.php';
include "config/db.php";
include "includes/header.php";

use Worldus\AuthService;
use Worldus\MysqliDatabase;

$db = new MysqliDatabase($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = AuthService::authenticate($db, $email, $password);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit();
    }

    echo "<p>Неверный логин или пароль</p>";
}
?>

<div class="container">
    <div class="card">
        <h2>Вход</h2>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Пароль" required><br><br>

            <button class="btn blue">Войти</button>
        </form>
    </div>
</div>

<?php include "includes/footer.php"; ?>