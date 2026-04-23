<?php

include "config/db.php";
include "includes/header.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit();
    } else {
        echo "<p>Неверный логин или пароль</p>";
    }
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