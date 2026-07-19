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
        if ($conn->connect_error) die("خطا در اتصال به دیتابیس");

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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ورود - کیش نوین</title>

<style>
* {
    box-sizing: border-box;
    font-family: sans-serif;
}

body {
    min-height: 100vh;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    background: linear-gradient(135deg, #007bff, #ff3b30);
    background-size: 400% 400%;
    animation: gradientBG 15s ease infinite;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.fade-in {
    opacity: 0;
    animation: fadeIn 0.8s ease forwards;
}
@keyframes fadeIn {
    to { opacity: 1; }
}

.login-box {
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(10px);
    padding: 40px;
    width: 100%;
    max-width: 420px;
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.25);
    position: relative;
    z-index: 10;
}

h1 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 24px;
    color: #333;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%,100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.alert {
    background: #fee2e2;
    color: #b91c1c;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 16px;
    font-size: 14px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #444;
}

input {
    width: 100%;
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid #ccc;
    transition: 0.3s;
}

input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 4px rgba(0,123,255,0.25);
}

button {
    width: 100%;
    padding: 14px;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    font-size: 16px;
    color: #fff;
    background: linear-gradient(to right, #007bff, #ff3b30);
    transition: 0.3s;
}

button:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
}

.floating {
    position: absolute;
    font-size: 3rem;
    opacity: 0.15;
    color: white;
    user-select: none;
}

@keyframes float1 {
    from { transform: translateY(0); }
    to { transform: translateY(-1200px) rotate(360deg); }
}
@keyframes float2 {
    from { transform: translateY(0); }
    to { transform: translateY(-1000px) rotate(-360deg); }
}
</style>
</head>

<body>

<div class="floating" style="left:10%; top:90%; animation:float1 25s linear infinite;">A</div>
<div class="floating" style="left:30%; top:85%; animation:float2 30s linear infinite;">📚</div>
<div class="floating" style="left:70%; top:95%; animation:float1 28s linear infinite;">🌍</div>

<div class="login-box fade-in">
    <h1>ورود</h1>

    <?php if ($msg): ?>
        <div class="alert"><?= $msg ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>نام کاربری</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
            <label>رمز عبور</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">ورود</button>
    </form>
</div>

</body>
</html>


    <!--<p class="mt-6 text-center text-gray-600">ثبت نام نکردید؟ <a href="register.php" class="text-blue-600 font-semibold underline hover:text-red-500 transition">ثبت نام</a></p>-->

