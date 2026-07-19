<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

require_once 'db.php';

// ---- گرفتن لیست ترم‌ها (distinct) ----
$termsResult = $mysqli->query("SELECT DISTINCT term FROM evaluations ORDER BY term DESC");
$terms = [];
while ($row = $termsResult->fetch_assoc()) {
    if (!empty($row['term'])) {
        $terms[] = $row['term'];
    }
}

// ترم انتخاب‌شده
$selectedTerm = $_GET['term'] ?? null;

// ---- کوئری گزارش با فیلتر ترم ----
$query = "
SELECT 
  t.id AS teacher_id,
  t.name AS teacher_name,
  COUNT(e.id) AS total_evals,
  ROUND(AVG((q1+q2+q3+q4+q5+q6+q7+q8+q9+q10)/10), 2) AS avg_total,
  ROUND(
    AVG(q1) + AVG(q2) + AVG(q3) + AVG(q4) + AVG(q5) +
    AVG(q6) + AVG(q7) + AVG(q8) + AVG(q9) + AVG(q10)
  , 2) AS sum_total,
  ROUND(AVG(q1), 2) AS avg_q1,
  ROUND(AVG(q2), 2) AS avg_q2,
  ROUND(AVG(q3), 2) AS avg_q3,
  ROUND(AVG(q4), 2) AS avg_q4,
  ROUND(AVG(q5), 2) AS avg_q5,
  ROUND(AVG(q6), 2) AS avg_q6,
  ROUND(AVG(q7), 2) AS avg_q7,
  ROUND(AVG(q8), 2) AS avg_q8,
  ROUND(AVG(q9), 2) AS avg_q9,
  ROUND(AVG(q10), 2) AS avg_q10
FROM teachers t
JOIN evaluations e ON t.id = e.teacher_id
";

// فیلتر فقط در صورت انتخاب ترم
if ($selectedTerm) {
    $query .= " WHERE e.term = '" . $mysqli->real_escape_string($selectedTerm) . "' ";
}

$query .= " GROUP BY t.id ORDER BY avg_total DESC ";

$report = $mysqli->query($query);

$questions = [
  "مفاهیم را واضح و قابل فهم توضیح می‌دهند",
  "فرصت کافی برای پرسش و رفع ابهام فراهم می‌کنند",
  "در فعالیت‌های گروهی نظارت دارند",
  "مرور و رفع اشکال جلسات قبل را انجام می‌دهند",
  "زبان‌آموزان را به صحبت انگلیسی در کلاس تشویق می‌کنند",
  "در گروه مجازی کلاس فعال هستند",
  "کتاب کار را بررسی و رفع اشکال می‌کنند",
  "از کتاب‌های مکمل استفاده می‌کنند",
  "به ایجاد انگیزه و حس مثبت اهمیت می‌دهند",
  "کلاس را منظم و دقیق برگزار می‌کنند"
];
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>گزارش کلی اساتید</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn&display=swap" rel="stylesheet">
  <style> body { font-family: 'Vazirmatn', sans-serif; } </style>
</head>
<body class="bg-green-50 min-h-screen py-10 px-4">
  <div class="max-w-6xl mx-auto bg-white p-8 rounded-3xl shadow-2xl">
    <h2 class="text-2xl font-bold text-green-700 text-center mb-6">گزارش کلی اساتید</h2>

    <!-- 🎯 فیلتر ترم -->
    <form method="get" class="mb-6 flex flex-wrap gap-4 justify-center">
      <?php foreach ($terms as $term): ?>
        <label class="flex items-center space-x-2 space-x-reverse cursor-pointer">
          <input type="radio" name="term" value="<?= htmlspecialchars($term) ?>" 
                 onchange="this.form.submit()" 
                 <?= $selectedTerm === $term ? 'checked' : '' ?>>
          <span><?= htmlspecialchars($term) ?></span>
        </label>
      <?php endforeach; ?>
      <label class="flex items-center space-x-2 space-x-reverse cursor-pointer">
        <input type="radio" name="term" value="" onchange="this.form.submit()" <?= !$selectedTerm ? 'checked' : '' ?>>
        <span>همه ترم‌ها</span>
      </label>
    </form>

    <table class="w-full text-sm border border-green-100">
      <thead class="bg-green-100">
        <tr>
          <th class="p-2">نام استاد</th>
          <th>تعداد ارزیابی</th>
          <th>مجموع کل</th>
          <?php for ($i = 1; $i <= 10; $i++): ?>
            <th>س<?= $i ?></th>
          <?php endfor; ?>
          <th>جزئیات</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($report->num_rows > 0): ?>
          <?php while ($row = $report->fetch_assoc()): ?>
            <tr class="hover:bg-green-50 border-b">
              <td class="p-2 font-semibold text-green-700">
                <a href="teacher-report.php?id=<?= $row['teacher_id'] ?>&term=<?= urlencode($selectedTerm) ?>" class="hover:underline">
                  <?= $row['teacher_name'] ?>
                </a>
              </td>
              <td class="text-center"><?= $row['total_evals'] ?></td>
              <td class="text-center font-bold text-green-600"><?= $row['sum_total'] ?></td>
              <?php for ($i = 1; $i <= 10; $i++): ?>
                <td class="text-center text-gray-600"><?= $row['avg_q' . $i] ?></td>
              <?php endfor; ?>
              <td class="text-center">
                <a href="teacher-report.php?id=<?= $row['teacher_id'] ?>&term=<?= urlencode($selectedTerm) ?>" 
                   class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-xl text-xs">نمایش</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="14" class="text-center p-4 text-red-500">⛔ هیچ داده‌ای برای این ترم یافت نشد</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
