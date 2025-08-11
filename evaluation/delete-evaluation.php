<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $mysqli->prepare("DELETE FROM evaluations WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '✅ ارزیابی با موفقیت حذف شد']);
    } else {
        echo json_encode(['success' => false, 'message' => '❌ خطا در حذف ارزیابی']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '❌ درخواست نامعتبر']);
}
