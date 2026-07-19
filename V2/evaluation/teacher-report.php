<?php
require_once 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die('❌ شناسه استاد معتبر نیست');
}
$teacher_id = (int)$_GET['id'];

$teacher = $mysqli->query("SELECT name FROM teachers WHERE id = $teacher_id")->fetch_assoc();
if (!$teacher) die('❌ استاد یافت نشد');

$evaluations = $mysqli->query("
  SELECT e.id, e.eval_date, e.term, 
         e.q1, e.q2, e.q3, e.q4, e.q5, 
         e.q6, e.q7, e.q8, e.q9, e.q10,
         c.level
  FROM evaluations e
  LEFT JOIN classes c ON e.class_id = c.id
  WHERE e.teacher_id = $teacher_id
  ORDER BY e.eval_date DESC
");

if ($evaluations->num_rows === 0) {
  die("<p class='text-red-600 text-center text-xl'>⛔ هیچ ارزیابی‌ای برای این استاد ثبت نشده است</p>");
}

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

// معانی نمرات
$meanings = [10=>'زیاد', 7=>'متوسط', 4=>'کم', 1=>'خیلی کم'];

// ------------------ آماده‌سازی داده‌ها برای نمودار خطی ------------------
mysqli_data_seek($evaluations, 0);
$levelsData = []; // [level][qIndex] = [scores]

while ($e = $evaluations->fetch_assoc()) {
  $level = $e['level'] ?? 'نامشخص';
  for ($i = 1; $i <= 10; $i++) {
    $levelsData[$level][$i][] = (int)$e["q$i"];
  }
}

// محاسبه میانگین
$chartData = [];
foreach ($levelsData as $level => $qs) {
  $avgScores = [];
  foreach ($qs as $i => $scores) {
    $avgScores[] = array_sum($scores) / count($scores);
  }
  $chartData[] = ["level" => $level, "scores" => $avgScores];
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>گزارش استاد - <?= htmlspecialchars($teacher['name']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Vazirmatn', sans-serif; }
    #popup { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; }
    #popup.show { display: flex; }
    #popup > div { background: white; padding: 1.5rem; border-radius: 1rem; max-width: 700px; width: 95%; max-height: 90vh; overflow-y: auto; position: relative; }
  </style>
</head>
<body class="bg-gray-50 min-h-screen py-10 px-4">
  <div class="max-w-5xl mx-auto bg-white p-8 rounded-3xl shadow-2xl">
    <h2 class="text-2xl font-bold text-green-700 text-center mb-6">گزارش عملکرد <?= htmlspecialchars($teacher['name']) ?></h2>

    <!-- 📊 نمودار خطی -->
    <div id="lineChart" class="mb-8"></div>

    <h3 class="text-lg font-semibold text-gray-800 mb-4">لیست ارزیابی‌ها</h3>
    <table class="w-full text-sm border">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2">تاریخ</th>
          <th>سطح</th>
          <th>ترم</th>
          <th>مجموع نمرات</th>
          <th class="text-center">جزئیات</th>
        </tr>
      </thead>
      <tbody>
        <?php mysqli_data_seek($evaluations, 0); ?>
        <?php while ($e = $evaluations->fetch_assoc()):
          $sum = 0;
          $details = [];
          for ($i = 1; $i <= 10; $i++) {
            $score = (int)$e["q$i"];
            $sum += $score;
            $meaning = $meanings[$score] ?? '-';
            $details[] = [
              "title" => "سؤال $i: {$questions[$i-1]}",
              "score" => $score,
              "meaning" => $meaning
            ];
          }
        ?>
        <tr class="border-b hover:bg-gray-50">
          <td class="p-2 text-center"><?= htmlspecialchars($e['eval_date']) ?></td>
          <td class="text-center"><?= htmlspecialchars($e['level'] ?? '-') ?></td>
          <td class="text-center"><?= htmlspecialchars($e['term'] ?? '-') ?></td>
          <td class="text-center font-semibold text-green-700"><?= $sum ?></td>
          <td class="text-center">
            <button onclick='showPopup(<?= json_encode($e['eval_date']) ?>, <?= json_encode($e['level'] ?? '-') ?>, <?= json_encode($e['term'] ?? '-') ?>, <?= json_encode($details, JSON_UNESCAPED_UNICODE) ?>)'
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-1 rounded-full text-xs">
              مشاهده
            </button>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <div id="popup">
    <div>
      <button onclick="closePopup()" style="position:absolute; top:0.5rem; left:0.5rem; font-size:1.5rem;">×</button>
      <div id="popupContent" class="space-y-3 text-sm leading-6 text-gray-700"></div>
      <div id="popupChart" class="mt-6"></div>
    </div>
  </div>

 <script>
  // 📊 رسم نمودار خطی در ابتدای صفحه
  const chartData = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE) ?>;
  const traces = chartData.map(c => ({
    type: 'scatter',
    mode: 'lines+markers',
    name: c.level,
    x: Array.from({length: c.scores.length}, (_, i) => `سؤال ${i+1}`),
    y: c.scores
  }));

  Plotly.newPlot('lineChart', traces, {
    title: {
      text: 'میانگین نمرات بر اساس سؤال‌ها و سطح‌ها',
      font: { size: 18 },
      pad: { t: 40 } // ✅ فاصله تیتر از نمودار
    },
    xaxis: { title: 'سؤالات' },
    yaxis: { title: 'میانگین نمره', range: [0, 10] }
  });

  // 📌 پاپ‌آپ جزئیات
  function showPopup(date, level, term, details) {
    const popup = document.getElementById('popup');
    const content = document.getElementById('popupContent');
    let list = "";
    const questions = [];
    const scores = [];
    details.forEach(d => {
      list += `<li><strong>${d.title}</strong> - امتیاز ${d.score} (${d.meaning})</li>`;
      questions.push(d.title);
      scores.push(d.score);
    });

    content.innerHTML = `
      <h4 class="text-lg font-bold text-green-700 mb-2">جزئیات ارزیابی</h4>
      <p><strong>تاریخ:</strong> ${date}</p>
      <p><strong>سطح:</strong> ${level}</p>
      <p><strong>ترم:</strong> ${term}</p>
      <ul class="mt-4 space-y-1">${list}</ul>
    `;

    Plotly.newPlot('popupChart', [{
      type: 'bar',
      x: questions,
      y: scores,
      marker: { color: 'rgba(34,139,34,0.7)' }
    }], {
      title: {
        text: 'نمودار ارزیابی',
        font: { size: 16 },
        pad: { t: 30 } // ✅ فاصله تیتر از نمودار پاپ‌آپ
      },
      yaxis: { title: 'امتیاز' },
      xaxis: { title: 'سؤالات' }
    });

    popup.classList.add('show');
  }

  function closePopup() {
    document.getElementById('popup').classList.remove('show');
  }
</script>

</body>
</html>
