<?php
require_once 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die('❌ شناسه استاد معتبر نیست');
}
$teacher_id = (int)$_GET['id'];

$teacher = $mysqli->query("SELECT name FROM teachers WHERE id = $teacher_id")->fetch_assoc();
if (!$teacher) die('❌ استاد یافت نشد');

$evaluations = $mysqli->query("
  SELECT e.*, c.level
  FROM evaluations e
  LEFT JOIN classes c ON e.class_id = c.id
  WHERE e.teacher_id = $teacher_id
  ORDER BY e.eval_date DESC
");

if ($evaluations->num_rows === 0) {
  die("<p class='text-red-600 text-center text-xl'>⛔ هیچ ارزیابی‌ای برای این استاد ثبت نشده است</p>");
}

// گرفتن مجموع نمرات هر سوال روی کل ارزیابی‌ها
$sum_result = $mysqli->query("
  SELECT
    SUM(q1) AS q1, SUM(q2) AS q2, SUM(q3) AS q3,
    SUM(q4) AS q4, SUM(q5) AS q5, SUM(q6) AS q6,
    SUM(q7) AS q7, SUM(q8) AS q8, SUM(q9) AS q9,
    SUM(q10) AS q10
  FROM evaluations
  WHERE teacher_id = $teacher_id
");
$sum_scores_raw = $sum_result->fetch_assoc();

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

// مجموع کل نمرات هر سوال در یک آرایه برای نمودار
$sum_scores = [];
foreach ($sum_scores_raw as $val) {
    $sum_scores[] = (int)$val;
}

// معانی نمرات برای نمایش جزییات
$meanings = [10=>'زیاد', 7=>'متوسط', 4=>'کم', 1=>'خیلی کم'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>گزارش استاد - <?= htmlspecialchars($teacher['name']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Vazirmatn', sans-serif; }
    #popup { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; }
    #popup.show { display: flex; }
    #popup > div { background: white; padding: 1.5rem; border-radius: 1rem; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; position: relative; }
  </style>
</head>
<body class="bg-gray-50 min-h-screen py-10 px-4">
  <div class="max-w-5xl mx-auto bg-white p-8 rounded-3xl shadow-2xl">
    <h2 class="text-2xl font-bold text-green-700 text-center mb-6">گزارش عملکرد <?= htmlspecialchars($teacher['name']) ?></h2>

    <!--<div class="text-center mb-6">-->
    <!--  <button onclick="generatePDF()"-->
    <!--    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl text-sm">-->
    <!--    📄 دانلود گزارش PDF-->
    <!--  </button>-->
    <!--</div>-->

    <!-- نمودار مجموع نمرات -->
    <div id="barChart" class="mb-8"></div>

    <h3 class="text-lg font-semibold text-gray-800 mb-4">لیست ارزیابی‌ها</h3>
    <table class="w-full text-sm border">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2">تاریخ</th>
          <th>سطح</th>
          <th>مجموع نمرات</th>
          <th class="text-center">جزئیات</th>
        </tr>
      </thead>
      <tbody>
        <?php mysqli_data_seek($evaluations, 0); ?>
        <?php while ($e = $evaluations->fetch_assoc()):
          $sum = 0;
          for ($i = 1; $i <= 10; $i++) {
            $sum += (int)$e["q$i"];
          }
          $details = '';
          for ($i = 1; $i <= 10; $i++) {
            $score = (int)$e["q$i"];
            $meaning = $meanings[$score] ?? '-';
            $details .= "<li><strong>سؤال $i:</strong> {$questions[$i-1]} - امتیاز $score ($meaning)</li>";
          }
        ?>
        <tr class="border-b hover:bg-gray-50">
          <td class="p-2 text-center"><?= htmlspecialchars($e['eval_date']) ?></td>
          <td class="text-center"><?= htmlspecialchars($e['level'] ?? '-') ?></td>
          <td class="text-center font-semibold text-green-700"><?= $sum ?></td>
          <td class="text-center">
            <button onclick="showPopup(`<?= addslashes($e['eval_date']) ?>`, `<?= addslashes($e['level'] ?? '-') ?>`, `<?= addslashes($details) ?>`) "
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
    </div>
  </div>

  <script>
    const questions = <?= json_encode($questions, JSON_UNESCAPED_UNICODE) ?>;
    const sums = <?= json_encode($sum_scores) ?>;

    Plotly.newPlot('barChart', [{
      type: 'bar',
      x: questions,
      y: sums,
      marker: { color: 'rgba(34,139,34,0.7)' }
    }], {
      yaxis: { title: 'مجموع نمرات' },
      xaxis: { title: 'سؤالات' },
      title: 'مجموع نمرات هر سؤال'
    });

    function showPopup(date, level, details) {
      const popup = document.getElementById('popup');
      const content = document.getElementById('popupContent');
      content.innerHTML = `
        <h4 class="text-lg font-bold text-green-700 mb-2">جزئیات ارزیابی</h4>
        <p><strong>تاریخ:</strong> ${date}</p>
        <p><strong>سطح:</strong> ${level}</p>
        <ul class="mt-4 space-y-1">${details}</ul>
      `;
      popup.classList.add('show');
    }

    function closePopup() {
      document.getElementById('popup').classList.remove('show');
    }

    async function generatePDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF('p', 'mm', 'a4');
      let y = 10;
      doc.setFontSize(16);
      doc.text(`گزارش ارزیابی استاد: <?= htmlspecialchars($teacher['name']) ?>`, 105, y, { align: 'center' });
      y += 10;
      const barCanvas = await html2canvas(document.querySelector('#barChart'));
      const barImg = barCanvas.toDataURL('image/png');
      doc.addImage(barImg, 'PNG', 15, y, 180, 100);
      y += 105;
      const tableCanvas = await html2canvas(document.querySelector('table'));
      const tableImg = tableCanvas.toDataURL('image/png');
      doc.addImage(tableImg, 'PNG', 10, y, 190, 0);
      doc.save(`گزارش‑<?= preg_replace('/[^A-Za-z0-9]/', '_', $teacher['name']) ?>.pdf`);
    }
  </script>
</body>
</html>
