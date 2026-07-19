<?php
require_once 'db.php';

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);

$stmt = $mysqli->prepare("
    SELECT e.*, t.name AS teacher_name, c.level, e.class_id, e.teacher_id 
    FROM evaluations e 
    JOIN teachers t ON e.teacher_id = t.id 
    JOIN classes c ON e.class_id = c.id 
    WHERE e.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'ارزیابی یافت نشد']);
    exit;
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

$response = [
    'success' => true,
    'teacher_id' => $data['teacher_id'],   // اضافه شد
    'teacher_name' => $data['teacher_name'],
    'class_id' => $data['class_id'],       // اضافه شد
    'level' => $data['level'],
    'eval_date' => $data['eval_date'],
    'questions' => []
];

for ($i = 1; $i <= 10; $i++) {
    $response['questions'][] = [
        'text' => $questions[$i - 1],
        'score' => $data["q$i"]
    ];
}

echo json_encode($response);
