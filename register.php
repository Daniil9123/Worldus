<?php
include "config/db.php";
include "includes/header.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (email, password) VALUES ('$email', '$password')";

    if ($conn->query($sql)) {
        echo "<p>Регистрация успешна!</p>";
    } else {
        echo "<p>Ошибка: пользователь уже существует</p>";
    }
}
?>

<div class="container">
    <div class="card">
        <h2>Регистрация</h2>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Пароль" required><br><br>

            <button class="btn green">Зарегистрироваться</button>
        </form>
    </div>
</div>

<?php include "includes/footer.php"; ?>