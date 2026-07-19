<?php
// بارگذاری فایل‌های PHPMailer (بدون Composer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'], $_SESSION['role'], $_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $subject && $message) {
        $mail = new PHPMailer(true);
        try {
            // تنظیمات SMTP سایت
            $mail->isSMTP();
            $mail->Host       = 'mail.kishnovinedu.ir';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'info@kishnovinedu.ir';
            $mail->Password   = 'bfHJC]6qx[um';    
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = 465;

            // مشخصات ایمیل
            $mail->setFrom('info@kishnovinedu.ir', 'سیستم آموزش');
            $mail->addAddress('behniya1394@gmail.com', 'مدیر آموزش');
            $mail->addReplyTo($email, $name);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = nl2br(htmlspecialchars($message));
            $mail->AltBody = $message;

            $mail->send();
            $success = 'پیام شما با موفقیت ارسال شد.';
        } catch (Exception $e) {
            $error = 'ارسال پیام با خطا مواجه شد: ' . $mail->ErrorInfo;
        }
    } else {
        $error = 'لطفاً همه فیلدها را پر کنید.';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>ارتباط با مدیر آموزش</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet" />
  <style>body { font-family: 'Vazirmatn', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center p-6">

  <main class="w-full max-w-xl bg-white shadow-md rounded p-6">
    <?php if($success): ?>
      <div class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= htmlspecialchars($success) ?></div>
    <?php elseif($error): ?>
      <div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block mb-1 font-medium">نام و نام خانوادگی</label>
        <input type="text" name="name" required class="w-full border rounded p-2" value="<?= htmlspecialchars($username) ?>">
      </div>

      <div>
        <label class="block mb-1 font-medium">ایمیل شما</label>
        <input type="email" name="email" required class="w-full border rounded p-2">
      </div>

      <div>
        <label class="block mb-1 font-medium">موضوع</label>
        <input type="text" name="subject" required class="w-full border rounded p-2">
      </div>

      <div>
        <label class="block mb-1 font-medium">پیام</label>
        <textarea name="message" rows="5" required class="w-full border rounded p-2"></textarea>
      </div>

      <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">ارسال پیام</button>
      <div class="mt-4 text-center">
        <a href="dashboard.php" class="text-sm text-indigo-600 underline">بازگشت به داشبورد</a>
      </div>
    </form>
  </main>
</body>
</html>
