<?php
require_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $class_id = (int)($_POST['class_id'] ?? 0);  // اضافه شده
    $teacher_id = (int)($_POST['teacher_id'] ?? 0); // اضافه شده
    $eval_date = $_POST['eval_date'] ?? '';

    if (!$class_id || !$teacher_id || !$id || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $eval_date)) {
        echo json_encode(['success' => false, 'message' => '❌ اطلاعات نامعتبر ارسال شده است']);
        exit;
    }

    $answers = [];
    for ($i = 1; $i <= 10; $i++) {
        $answers[$i] = isset($_POST["q$i"]) ? (int)$_POST["q$i"] : null;
    }

    $stmt = $mysqli->prepare("
        UPDATE evaluations 
        SET class_id = ?, teacher_id = ?, eval_date = ?, 
            q1 = ?, q2 = ?, q3 = ?, q4 = ?, q5 = ?, 
            q6 = ?, q7 = ?, q8 = ?, q9 = ?, q10 = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("iissiiiiiiiii",
        $class_id, $teacher_id, $eval_date,
        $answers[1], $answers[2], $answers[3], $answers[4], $answers[5],
        $answers[6], $answers[7], $answers[8], $answers[9], $answers[10],
        $id
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '✅ ارزیابی با موفقیت بروزرسانی شد']);
    } else {
        echo json_encode(['success' => false, 'message' => '❌ خطا در بروزرسانی ارزیابی']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '❌ درخواست نامعتبر']);
}
