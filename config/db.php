<?php
$host = "localhost";
$user = "root";
$pass = ""; // В XAMPP обычно пусто
$dbname = "worldus";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
?>