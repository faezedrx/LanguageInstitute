<?php
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) die("خطا در اتصال به دیتابیس: " . $conn->connect_error);

// تابع ساخت پسورد رندوم
function generatePassword($length = 8) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    return substr(str_shuffle($chars), 0, $length);
}

// فقط یوزرهایی که رولشون teach هست
$result = $conn->query("SELECT id, username FROM users WHERE role='teach'");

$output = [];

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $username = $row['username'];

    // پسورد رندوم
    $plain_pass = generatePassword();
    $hashed = password_hash($plain_pass, PASSWORD_DEFAULT);

    // آپدیت پسورد در دیتابیس
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=? AND role='teach'");
    $stmt->bind_param("si", $hashed, $id);
    $stmt->execute();

    // اضافه کردن به خروجی JSON
    $output[] = [
        "username" => $username,
        "password" => $plain_pass
    ];
}

$conn->close();

// ذخیره در فایل
file_put_contents("users_passwords.json", json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "✅ فایل users_passwords.json ساخته شد فقط برای role='teach' !";
