<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_set_cookie_params([
    'path' => '/',
    'httponly' => true,
    'secure' => false, // اگر SSL داری true کن
    'samesite' => 'Lax'
]);
session_start();

require_once __DIR__ . '/evaluation/db.php'; // مسیر دیتابیس
$mysqli->set_charset("utf8mb4");

// بررسی ورود
if (!isset($_SESSION['user_id'], $_SESSION['role'], $_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 48) {
    $_SESSION['username'] = 'admin';
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$teacher_id = 0;

// فقط برای teach آیدی استاد رو می‌گیریم
if ($role === 'teach') {
    $username_escaped = $mysqli->real_escape_string($username);
    $query = "SELECT id FROM teachers WHERE username = '$username_escaped'";
    $result = $mysqli->query($query);
    if ($result && $result->num_rows > 0) {
        $teacher_id = $result->fetch_assoc()['id'];
    }
}

// تعیین لینک ارزیابی
switch ($role) {
    case 'edu':
        $evalLink = '/evaluation/index0.php';
        break;
    case 'office':
        $evalLink = '/evaluation/index.php';
        break;
    case 'teach':
        $evalLink = "/evaluation/teacher-report.php?id={$teacher_id}";
        break;
    default:
        $evalLink = '#';
}

// تعیین لینک داینامیک «تعیین سطح» بر اساس user_id
switch ($user_id) {
    case 51:
        $levelLink = '/level/index.php?id=3';
        break;
    case 1:
        $levelLink = '/level/index.php?id=4';
        break;
    case 2:
        $levelLink = '/level/index.php?id=2';
        break;
    case 50:
        $levelLink = '/level/index.php?id=5';
        break;
    case 48:
        $levelLink = '/level/index.php?id=1';
        break;
    default:
        $levelLink = '/level/index.php'; // لینک پیش‌فرض
}
$specialUsers = [51, 1, 2, 50];
$showLevelCard = in_array($role, ['office']) || in_array($user_id, $specialUsers);
$levelCardLink = $levelLink;

// --- کوئری رتبه‌بندی اساتید ---
$rankQuery = "
SELECT 
  t.id AS teacher_id,
  t.name AS teacher_name,
  ROUND(
    AVG(q1) + AVG(q2) + AVG(q3) + AVG(q4) + AVG(q5) +
    AVG(q6) + AVG(q7) + AVG(q8) + AVG(q9) + AVG(q10)
  , 2) AS sum_total
FROM teachers t
JOIN evaluations e ON t.id = e.teacher_id
GROUP BY t.id
ORDER BY sum_total DESC
";

$rankResult = $mysqli->query($rankQuery);
$teachers = [];
if ($rankResult) {
    while ($row = $rankResult->fetch_assoc()) {
        $teachers[] = $row;
    }
}

// محاسبه رتبه‌ها با مدل Dense Ranking (بدون پرش رتبه‌ها)
$rankedTeachers = [];
$rank = 1;
$prevScore = null;

foreach ($teachers as $index => $teacher) {
    if ($prevScore !== null && $teacher['sum_total'] < $prevScore) {
        $rank++; // فقط وقتی امتیاز کمتر شد، رتبه افزایش پیدا می‌کنه
    }
    if ($rank > 3) break; // فقط سه رتبه اول
    $teacher['rank'] = $rank;
    $prevScore = $teacher['sum_total'];
    $rankedTeachers[] = $teacher;
}

// گروه‌بندی بر اساس رتبه برای نمایش
$ranksGrouped = [];
foreach ($rankedTeachers as $teacher) {
    $r = $teacher['rank'];
    if ($r > 3) continue;
    $ranksGrouped[$r][] = $teacher;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>داشبورد | خوش آمدید <?= htmlspecialchars($username) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Vazirmatn', sans-serif; }
    @keyframes fadeIn { from {opacity: 0;} to {opacity: 1;} }
    .animate-fade-in { animation: fadeIn 0.6s ease-in-out forwards; }
  </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-pink-50 min-h-screen text-gray-800">

  <header class="bg-white shadow-md p-4 flex justify-between items-center sticky top-0 z-10">
    <h1 class="text-2xl font-bold text-indigo-700 flex items-center gap-2">
      <i data-lucide="layout-dashboard" class="w-6 h-6"></i> داشبورد
    </h1>
    <div class="flex items-center gap-4">
      <span class="text-gray-600">سلام، <strong><?= htmlspecialchars($username) ?></strong></span>
      <a href="logout.php" class="bg-red-100 text-red-600 px-3 py-1 rounded hover:bg-red-200 transition text-sm">خروج</a>
    </div>
  </header>

  <main class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 animate-fade-in">

    <?php if (in_array($role, ['teach', 'office', 'edu'])): ?>
      <a href="<?= $evalLink ?>" class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition group">
        <div class="flex items-center gap-3 mb-4 text-indigo-600">
          <i data-lucide="bar-chart-3" class="w-6 h-6 group-hover:scale-110 transition"></i>
          <h2 class="text-xl font-bold">نظرسنجی</h2>
        </div>
        <p class="text-gray-500 text-sm">مدیریت و مشاهده نظرسنجی‌ها</p>
      </a>
    <?php endif; ?>

    <?php if ($role === 'teach'): ?>
      <a href="/dropout/dropout-report.php" class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition group">
        <div class="flex items-center gap-3 mb-4 text-indigo-600">
          <i data-lucide="user-minus" class="w-6 h-6 group-hover:scale-110 transition"></i>
          <h2 class="text-xl font-bold">ریزشی‌ها</h2>
        </div>
        <p class="text-gray-500 text-sm">مدیریت دانشجویان ریزشی</p>
      </a>
    <?php endif; ?>

    <?php if (in_array($role, ['office', 'edu'])): ?>
      <a href="/dropout" class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition group">
        <div class="flex items-center gap-3 mb-4 text-indigo-600">
          <i data-lucide="user-minus" class="w-6 h-6 group-hover:scale-110 transition"></i>
          <h2 class="text-xl font-bold">ریزشی‌ها</h2>
        </div>
        <p class="text-gray-500 text-sm">مدیریت دانشجویان ریزشی</p>
      </a>
    <?php endif; ?>

    <?php if ($showLevelCard): ?>
      <a href="<?= htmlspecialchars($levelCardLink) ?>" class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition group">
        <div class="flex items-center gap-3 mb-4 text-indigo-600">
          <i data-lucide="search-check" class="w-6 h-6 group-hover:scale-110 transition"></i>
          <h2 class="text-xl font-bold">تعیین سطح</h2>
        </div>
        <p class="text-gray-500 text-sm">ثبت و مشاهده تعیین سطح دانشجویان</p>
      </a>
    <?php endif; ?>

    <!-- کارت رتبه بندی اساتید -->
    <?php if (!empty($ranksGrouped)): ?>
      <section class="bg-white rounded-2xl shadow-md p-6 hover:shadow-xl transition group col-span-full">
        <div class="flex items-center gap-3 mb-4 text-indigo-600">
          <i data-lucide="award" class="w-6 h-6 group-hover:scale-110 transition"></i>
          <h2 class="text-xl font-bold">رتبه‌بندی اساتید (اول تا سوم)</h2>
        </div>

        <?php 
        $rankNames = ['اول', 'دوم', 'سوم'];
        for ($i = 1; $i <= 3; $i++): 
          if (!empty($ranksGrouped[$i])):
        ?>
          <div class="mb-4">
            <h3 class="text-lg font-semibold mb-2 text-indigo-700">
              <?= $rankNames[$i - 1] ?> (رتبه <?= $i ?><?= count($ranksGrouped[$i]) > 1 ? " مشترک" : "" ?>)
            </h3>
            <ul class="list-disc pr-6 text-gray-700">
              <?php foreach ($ranksGrouped[$i] as $teacher): ?>
                <li class="flex justify-between items-center">
                  <span><?= htmlspecialchars($teacher['teacher_name']) ?></span>
                  <span class="text-green-600 font-bold"><?= $teacher['sum_total'] ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php 
          endif;
        endfor; 
        ?>

      </section>
    <?php endif; ?>

  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
