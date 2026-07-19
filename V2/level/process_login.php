<?php
session_start(); // حتما اول فایل بیاد
include "db.php";

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    // اگر پسورد ساده است:
    if ($password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];  // اینجا ست کن
        header("Location: index.php");
        exit;  // حتما exit بزار بعد ریدایرکت
    } else {
        echo "نام کاربری یا رمز عبور اشتباه است";
    }
} else {
    echo "نام کاربری یا رمز عبور اشتباه است";
}
