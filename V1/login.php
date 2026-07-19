<?php
session_start();
require_once 'config.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $msg = 'لطفا همه فیلدها را پر کنید.';
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset("utf8mb4");
        if ($conn->connect_error) die("خطا در اتصال به دیتابیس: " . $conn->connect_error);

        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $hashed_password, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                $_SESSION['logged_in'] = true;

                header("Location: dashboard.php");
                exit;
            } else {
                $msg = "رمز عبور اشتباه است.";
            }
        } else {
            $msg = "نام کاربری یافت نشد.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>ورود کاربر</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-green-400 via-blue-500 to-purple-600 min-h-screen flex items-center justify-center font-sans">

<div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
    <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">ورود</h1>

    <?php if ($msg): ?>
        <div class="mb-4 p-3 rounded text-sm bg-red-100 text-red-700">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <form method="post" class="space-y-5">
        <div>
            <label class="block mb-1 font-medium text-gray-700">نام کاربری</label>
            <input type="text" name="username" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" />
        </div>

        <div>
            <label class="block mb-1 font-medium text-gray-700">رمز عبور</label>
            <input type="password" name="password" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400" />
        </div>

        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition">ورود</button>
    </form>

    <!--<p class="mt-4 text-center text-gray-600">ثبت نام نکردید؟ <a href="register.php" class="text-green-700 underline">ثبت نام</a></-->
</div>

</body>
</html>
