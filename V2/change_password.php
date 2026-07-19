<?php

session_start();
require_once 'config.php'; // باید ثابت‌های DB_* تعریف شده باشه

// تنظیمات سشن مشابه داشبورد
session_set_cookie_params([
    'path' => '/',
    'httponly' => true,
    'secure' => false, // اگر SSL دارید true بگذارید
    'samesite' => 'Lax'
]);

// نمایش خطا برای توسعه — در تولید غیرفعال کنید
ini_set('display_errors', 0);
error_reporting(E_ALL);

$msg = '';
$success = '';

// فقط کاربر وارد شده می‌تونه رمز عوض کنه
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// تابع ساده برای بررسی پیچیدگی رمز عبور
function validate_password_strength($pwd) {
    // حداقل 8 کاراکتر، یک حرف بزرگ، یک حرف کوچک، یک عدد
    if (strlen($pwd) < 8) return 'رمز باید حداقل 8 کاراکتر باشد.';
    if (!preg_match('/[A-Z]/u', $pwd)) return 'رمز باید حداقل یک حرف بزرگ داشته باشد.';
    if (!preg_match('/[a-z]/u', $pwd)) return 'رمز باید حداقل یک حرف کوچک داشته باشد.';
    if (!preg_match('/[0-9]/u', $pwd)) return 'رمز باید حداقل یک عدد داشته باشد.';
    // در صورت نیاز شرط‌های دیگری اضافه کنید
    return '';
}

// تولید/بررسی توکن CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // بررسی توکن
    $posted_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $posted_token)) {
        $msg = 'خطا: توکن نامعتبر. لطفا صفحه را بازنشانی کنید.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!$current || !$new || !$confirm) {
            $msg = 'لطفا همه فیلدها را کامل کنید.';
        } elseif ($new !== $confirm) {
            $msg = 'رمز جدید و تکرار آن یکسان نیستند.';
        } else {
            $strength_err = validate_password_strength($new);
            if ($strength_err) {
                $msg = $strength_err;
            } else {
                // اتصال به دیتابیس
                $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $conn->set_charset('utf8mb4');
                if ($conn->connect_error) {
                    $msg = 'خطا در اتصال به دیتابیس.';
                } else {
                    $user_id = (int)$_SESSION['user_id'];

                    // دریافت پسورد کنونی
                    $stmt = $conn->prepare('SELECT password FROM users WHERE id = ?');
                    $stmt->bind_param('i', $user_id);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows !== 1) {
                        $msg = 'کاربر یافت نشد.';
                    } else {
                        $stmt->bind_result($hashed_password);
                        $stmt->fetch();

                        if (!password_verify($current, $hashed_password)) {
                            $msg = 'رمز عبور فعلی اشتباه است.';
                        } else {
                            // هش کردن رمز جدید و آپدیت
                            $new_hashed = password_hash($new, PASSWORD_DEFAULT);

                            $update = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
                            $update->bind_param('si', $new_hashed, $user_id);

                            if ($update->execute()) {
                                // بازسازی سشن برای امنیت
                                session_regenerate_id(true);

                                // با پاک کردن توکن CSRF از دوباره ارسال جلوگیری کن
                                unset($_SESSION['csrf_token']);

                                $success = 'رمز عبور با موفقیت تغییر کرد.';
                            } else {
                                $msg = 'خطا هنگام ذخیره‌سازی رمز جدید.';
                            }
                            $update->close();
                        }
                    }

                    $stmt->close();
                    $conn->close();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>تغییر رمز عبور</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center font-sans">

<div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
    <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">تغییر رمز عبور</h1>

    <?php if ($msg): ?>
        <div class="mb-4 p-3 rounded text-sm bg-red-100 text-red-700"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="mb-4 p-3 rounded text-sm bg-green-100 text-green-700"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>" />

        <div>
            <label class="block mb-1 font-medium text-gray-700">رمز عبور فعلی</label>
            <input type="password" name="current_password" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
        </div>

        <div>
            <label class="block mb-1 font-medium text-gray-700">رمز عبور جدید</label>
            <input type="password" name="new_password" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
            <p class="text-xs text-gray-500 mt-1">حداقل 8 کاراکتر، شامل حرف بزرگ، حرف کوچک و عدد.</p>
        </div>

        <div>
            <label class="block mb-1 font-medium text-gray-700">تکرار رمز جدید</label>
            <input type="password" name="confirm_password" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
        </div>

        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 transition">تغییر رمز</button>
    </form>

    <div class="mt-4 text-center">
        <a href="dashboard.php" class="text-sm text-indigo-600 underline">بازگشت به داشبورد</a>
    </div>
</div>

</body>
</html>
