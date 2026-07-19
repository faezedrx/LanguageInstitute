<?php
require_once 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'شناسه استاد معتبر نیست']);
    exit;
}

$teacher_id = (int)$_GET['id'];

// کوئری گرفتن میانگین ماهانه نمرات
$query = "
SELECT 
    DATE_FORMAT(eval_date, '%Y-%m') AS month,
    ROUND(AVG((q1+q2+q3+q4+q5+q6+q7+q8+q9+q10)/10), 2) AS avg_score
FROM evaluations
WHERE teacher_id = ?
GROUP BY month
ORDER BY month
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$labels = [];
$values = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['month'];
    $values[] = (float)$row['avg_score'];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['labels' => $labels, 'values' => $values], JSON_UNESCAPED_UNICODE);
