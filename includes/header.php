<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Worldus</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="navbar">
    <div class="logo">
        <img src="assets/images/logo.png" alt="Worldus">
        <span>WORLDUS</span>
    </div>

    <div class="nav-buttons">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn">Регистрация</a>
            <a href="login.php" class="btn">Вход</a>
        <?php else: ?>
            <a href="categories.php" class="btn">Категории</a>
            <a href="logout.php" class="btn logout">Выход</a>
        <?php endif; ?>
    </div>
</div>