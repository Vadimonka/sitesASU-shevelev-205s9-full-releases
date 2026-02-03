<?php
$host = 'localhost';
$db   = 'it_shop';
$user = 'root';
$pass = ''; // Твой пароль от базы

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
?>