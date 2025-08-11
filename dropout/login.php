<?php
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // اگر قبلا لاگین بود، مستقیم میریم داشبورد
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'نام کاربری یا رمز عبور اشتباه است';
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>صفحه ورود</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Vazirmatn', sans-serif; }
  </style>
</head>
<body class="bg-gradient-to-tr from-green-50 to-white min-h-screen flex items-center justify-center">
  <div class="w-full max-w-md p-6 bg-white rounded-2xl shadow-xl space-y-6">
    <h1 class="text-3xl font-bold text-green-600 text-center">ورود به داشبورد</h1>

    <?php if ($error): ?>
      <p class="text-red-500 text-center"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" class="space-y-4" action="">
      <input type="text" name="username" placeholder="نام کاربری" required
             class="w-full p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400" />
      <input type="password" name="password" placeholder="رمز عبور" required
             class="w-full p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400" />
      <button type="submit"
              class="w-full bg-green-500 hover:bg-green-600 text-white p-3 rounded-xl font-semibold transition">
        ورود
      </button>
    </form>
  </div>
</body>
</html>
