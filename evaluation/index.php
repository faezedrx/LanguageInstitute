<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

// دریافت لیست اساتید و کلاس‌ها
$teachers = $mysqli->query("SELECT * FROM teachers");
$classes = $mysqli->query("SELECT id, level FROM classes WHERE level IS NOT NULL");

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

// ثبت فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $class_id = $_POST['class_id'];
    $eval_date = $_POST['eval_date'];

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eval_date)) {
        echo '<div class="text-red-600 text-center p-4">فرمت تاریخ معتبر نیست. لطفاً به صورت YYYY-MM-DD وارد کنید ❌</div>';
        exit;
    }

    $answers = [];
    for ($i = 1; $i <= 10; $i++) {
        $answers[] = isset($_POST["q$i"]) ? (int)$_POST["q$i"] : NULL;
    }

    $stmt = $mysqli->prepare("INSERT INTO evaluations (class_id, teacher_id, eval_date, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissiiiiiiiii", $class_id, $teacher_id, $eval_date, ...$answers);
    $stmt->execute();
    echo '<div class="text-green-600 text-center p-4">با موفقیت ثبت شد ✅</div>';
}

// صفحه‌بندی
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$perPage = 5;
$offset = ($page - 1) * $perPage;

// تعداد کل ارزیابی‌ها
$total_result = $mysqli->query("SELECT COUNT(*) AS total FROM evaluations");
$total_row = $total_result->fetch_assoc();
$total_evaluations = $total_row['total'];
$total_pages = ceil($total_evaluations / $perPage);

// دریافت ارزیابی‌ها
$evaluations = $mysqli->query("SELECT e.id, e.eval_date, t.name AS teacher_name, c.level 
                               FROM evaluations e 
                               JOIN teachers t ON e.teacher_id = t.id 
                               JOIN classes c ON e.class_id = c.id 
                               ORDER BY e.id DESC 
                               LIMIT $perPage OFFSET $offset");
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>فرم ارزیابی</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Vazirmatn', sans-serif; }
    #popup {
      position: fixed;
      inset: 0;
      background-color: rgba(0,0,0,0.5);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }
    #popup.show {
      display: flex;
    }
    #popup > div {
      background: white;
      padding: 1.5rem;
      border-radius: 1rem;
      max-width: 600px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
      position: relative;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-white min-h-screen py-10 px-4">
  <div class="max-w-4xl mx-auto bg-white p-8 rounded-3xl shadow-2xl">
    <h2 class="text-2xl font-bold text-green-700 text-center mb-6">فرم ارزیابی عملکرد استاد</h2>
    <form method="POST" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <select name="teacher_id" required class="border p-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400">
          <option value="">انتخاب استاد</option>
          <?php while($row = $teachers->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
          <?php endwhile; ?>
        </select>

        <select name="class_id" required class="border p-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400">
          <option value="">سطح</option>
          <?php while($row = $classes->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= $row['level'] ?></option>
          <?php endwhile; ?>
        </select>

        <input type="text" name="eval_date" required
               placeholder="مثلاً 2025-08-04"
               pattern="\d{4}-\d{2}-\d{2}"
               class="border p-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400" />
      </div>

      <div class="space-y-5">
        <?php for ($i = 1; $i <= 10; $i++): ?>
          <div class="bg-gray-50 p-4 rounded-xl border flex items-center gap-8">
            <label class="font-semibold text-gray-700 flex-1 text-right">سؤال <?= $i ?>: <?= $questions[$i - 1] ?></label>
            <div class="flex gap-6">
              <?php foreach ([10 => 'زیاد', 7 => 'متوسط', 4 => 'کم', 1 => 'خیلی کم'] as $val => $label): ?>
                <label class="flex items-center gap-2 flex-row-reverse">
                  <input type="radio" name="q<?= $i ?>" value="<?= $val ?>" required class="text-green-600" />
                  <span><?= $label ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endfor; ?>
      </div>

      <div class="text-center pt-6">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-xl font-bold transition-all duration-200">ثبت ارزیابی</button>
      </div>
    </form>
  </div>

  <div class="max-w-4xl mx-auto bg-white mt-10 p-6 rounded-2xl shadow-xl border border-green-100">
    <h3 class="text-xl font-bold text-green-700 mb-4 text-center">آخرین ارزیابی‌ها</h3>
    <ul class="space-y-3 text-sm">
      <?php while ($e = $evaluations->fetch_assoc()): ?>
        <li class="border p-3 rounded-xl hover:bg-green-50 transition cursor-pointer"
            onclick="showDetails(<?= $e['id'] ?>)">
          <strong><?= $e['teacher_name'] ?></strong> - سطح <?= $e['level'] ?> - <?= $e['eval_date'] ?>
        </li>
      <?php endwhile; ?>
    </ul>

    <!-- صفحه‌بندی -->
    <div class="flex justify-center mt-6 gap-2">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>"
           class="px-3 py-1 rounded-lg border <?= $i == $page ? 'bg-green-600 text-white' : 'bg-white text-green-700 hover:bg-green-100' ?>">
           <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  </div>

  <!-- پاپ‌آپ نمایش یا ویرایش -->
  <div id="popup">
    <div>
      <button onclick="closePopup()" style="position:absolute; top:0.5rem; left:0.5rem; font-size:1.5rem;">×</button>
      <div id="popupContent" class="space-y-3 text-sm leading-6 text-gray-700"></div>
    </div>
  </div>

  <script>
    const scoreMeanings = { 10: 'زیاد', 7: 'متوسط', 4: 'کم', 1: 'خیلی کم' };

    function showPopup() {
      document.getElementById('popup').classList.add('show');
    }

    function closePopup() {
      document.getElementById('popup').classList.remove('show');
    }

    function showDetails(id) {
      fetch('get-evaluation.php?id=' + id)
        .then(res => res.json())
        .then(data => {
          const content = document.getElementById('popupContent');
          content.innerHTML = `
            <h4 class="text-lg font-bold text-green-700 mb-4">جزئیات ارزیابی</h4>
            <p><strong>استاد:</strong> ${data.teacher_name}</p>
            <p><strong>سطح:</strong> ${data.level}</p>
            <p><strong>تاریخ:</strong> ${data.eval_date}</p>
            <ul class="mt-4 space-y-1">
              ${data.questions.map((q, i) => 
                `<li><strong>سؤال ${i + 1}:</strong> ${q.text} - امتیاز ${q.score} (${scoreMeanings[q.score] || ''})</li>`
              ).join('')}
            </ul>
            <div class="mt-6 flex justify-end gap-4">
              <button onclick="editEvaluation(${id})" class="bg-yellow-400 px-4 py-2 rounded text-white hover:bg-yellow-500">ویرایش</button>
              <button onclick="deleteEvaluation(${id})" class="bg-red-600 px-4 py-2 rounded text-white hover:bg-red-700">حذف</button>
            </div>
          `;
          showPopup();
        });
    }

    function deleteEvaluation(id) {
      if(confirm('آیا مطمئن هستید که می‌خواهید این ارزیابی را حذف کنید؟')) {
        fetch('delete-evaluation.php?id=' + id, { method: 'POST' })
          .then(res => res.json())
          .then(data => {
            alert(data.message);
            if(data.success) {
              closePopup();
              location.reload();
            }
          });
      }
    }

    function editEvaluation(id) {
      fetch('get-evaluation.php?id=' + id)
        .then(res => res.json())
        .then(data => {
          const content = document.getElementById('popupContent');
          content.innerHTML = `
            <h4 class="text-lg font-bold text-green-700 mb-4">ویرایش ارزیابی</h4>
            <form id="editForm">
              <div class="mb-4">
                <label class="block font-semibold mb-1">استاد:</label>
                <input type="text" value="${data.teacher_name}" disabled class="border p-2 rounded w-full bg-gray-100" />
              </div>
              <div class="mb-4">
                <label class="block font-semibold mb-1">سطح:</label>
                <input type="text" value="${data.level}" disabled class="border p-2 rounded w-full bg-gray-100" />
              </div>
              <div class="mb-4">
                <label class="block font-semibold mb-1" for="eval_date_edit">تاریخ ارزیابی:</label>
                <input type="text" id="eval_date_edit" name="eval_date" value="${data.eval_date}" pattern="\\d{4}-\\d{2}-\\d{2}" class="border p-2 rounded w-full" required />
              </div>
              <div class="space-y-3 max-h-64 overflow-y-auto">
                ${data.questions.map((q, i) => 
                  `<div>
                    <label class="font-semibold block mb-1">سؤال ${i + 1}: ${q.text}</label>
                    <select name="q${i + 1}" required class="border p-2 rounded w-full">
                      <option value="10" ${q.score == 10 ? 'selected' : ''}>زیاد</option>
                      <option value="7" ${q.score == 7 ? 'selected' : ''}>متوسط</option>
                      <option value="4" ${q.score == 4 ? 'selected' : ''}>کم</option>
                      <option value="1" ${q.score == 1 ? 'selected' : ''}>خیلی کم</option>
                    </select>
                  </div>`
                ).join('')}
              </div>
              <div class="mt-6 flex justify-end gap-4">
                <button type="submit" class="bg-green-600 px-4 py-2 rounded text-white hover:bg-green-700">ذخیره</button>
                <button type="button" onclick="closePopup()" class="bg-gray-400 px-4 py-2 rounded text-white hover:bg-gray-500">انصراف</button>
              </div>
            </form>
          `;

          document.getElementById('editForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('id', id);
            fetch('edit-evaluation.php', {
              method: 'POST',
              body: formData
            })
            .then(res => res.json())
            .then(res => {
              alert(res.message);
              if(res.success) {
                closePopup();
                location.reload();
              }
            });
          };
        });
    }
  </script>
        <div class="text-center pt-6">
            <a href="index0.php" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-xl font-bold transition-all duration-200"></a>
      </div>
</body>
</html>
