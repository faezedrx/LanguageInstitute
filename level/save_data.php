<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';
$isAdmin = ($username === 'admin');
$level_id = isset($_POST['level_id']) ? intval($_POST['level_id']) : 0;
if ($level_id === 5) {
    $username = 'mrmirhashemi';
}

// لیست داده‌ها با صفحه‌بندی
if (isset($_GET['action']) && $_GET['action'] === 'list') {
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 7;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    if ($limit <= 0) $limit = 7;
    if ($page <= 0) $page = 1;
    $offset = ($page - 1) * $limit;

    $searchCondition = ($search !== '') ? " AND (firstname LIKE '%$search%' OR lastname LIKE '%$search%' OR level LIKE '%$search%')" : '';

    // تعداد کل
    $countSql = "SELECT COUNT(*) as total FROM user_data WHERE 1=1 $searchCondition";
    $countResult = $conn->query($countSql);
    if (!$countResult) {
        echo json_encode(['error' => $conn->error]);
        exit;
    }
    $totalRows = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $limit);

    // داده صفحه
    $sql = "
        SELECT ud.*, u.username 
        FROM user_data ud
        LEFT JOIN users u ON ud.user_id = u.id
        WHERE 1=1 $searchCondition
        ORDER BY ud.created_at DESC
        LIMIT $limit OFFSET $offset
    ";

    $result = $conn->query($sql);
    if (!$result) {
        echo json_encode(['error' => $conn->error]);
        exit;
    }
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    echo json_encode([
        'rows' => $rows,
        'totalPages' => $totalPages,
        'currentPage' => $page,
    ]);
    exit;
}

// گرفتن رکورد برای ویرایش
if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($isAdmin) {
        $stmt = $conn->prepare("SELECT * FROM user_data WHERE id=?");
        $stmt->bind_param("i", $id);
    } else {
        $stmt = $conn->prepare("SELECT * FROM user_data WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $id, $user_id);
    }
    if (!$stmt->execute()) {
        echo json_encode(['error' => $stmt->error]);
        exit;
    }
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    if (!$data) {
        echo json_encode(['error' => 'not_found']);
    } else {
        echo json_encode($data);
    }
    exit;
}

// حذف رکورد
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($isAdmin) {
        $stmt = $conn->prepare("DELETE FROM user_data WHERE id=?");
        $stmt->bind_param("i", $id);
    } else {
        $stmt = $conn->prepare("DELETE FROM user_data WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $id, $user_id);
    }
    if (!$stmt->execute()) {
        echo json_encode(['error' => $stmt->error]);
        exit;
    }
    echo json_encode([
        'message' => $stmt->affected_rows > 0 ? '🗑️ اطلاعات با موفقیت حذف شد' : '❌ حذف انجام نشد یا مجاز نیستید'
    ]);
    exit;
}

// ذخیره یا بروزرسانی
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $fname = trim($_POST['firstname'] ?? '');
    $lname = trim($_POST['lastname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $age = isset($_POST['age']) ? intval($_POST['age']) : 0;
    $description = trim($_POST['description'] ?? '');
    $registered = (isset($_POST['registered']) && $_POST['registered'] === 'on' && $isAdmin) ? 1 : 0;

    if ($fname === '' || $lname === '' || $phone === '' || $level === '' || $age <= 0) {
        echo json_encode(['message' => 'لطفا همه فیلدهای اجباری را پر کنید']);
        exit;
    }

    // بررسی رکورد تکراری
    $duplicateCheckSql = "
        SELECT ud.*, u.username 
        FROM user_data ud
        LEFT JOIN users u ON ud.user_id = u.id
        WHERE ud.firstname=? AND ud.lastname=?
    ";
    $stmtDup = $conn->prepare($duplicateCheckSql);
    if (!$stmtDup) {
        echo json_encode(['error' => $conn->error]);
        exit;
    }
    $stmtDup->bind_param("ss", $fname, $lname);
    if (!$stmtDup->execute()) {
        echo json_encode(['error' => $stmtDup->error]);
        exit;
    }
    $dupResult = $stmtDup->get_result();
    $duplicateRecord = $dupResult->fetch_assoc();

    if ($duplicateRecord && ($id === 0 || $duplicateRecord['id'] != $id)) {
        echo json_encode([
            'message' => '⚠️ نام و نام خانوادگی تکراری است!',
            'duplicate' => $duplicateRecord
        ]);
        exit;
    }

    if ($id > 0) {
        if ($isAdmin) {
            $stmt = $conn->prepare("UPDATE user_data SET firstname=?, lastname=?, phone=?, level=?, age=?, description=?, registered=? WHERE id=?");
            if (!$stmt) {
                echo json_encode(['error' => $conn->error]);
                exit;
            }
            $stmt->bind_param("ssssisii", $fname, $lname, $phone, $level, $age, $description, $registered, $id);
        } else {
            $stmt = $conn->prepare("UPDATE user_data SET firstname=?, lastname=?, phone=?, level=?, age=?, description=? WHERE id=? AND user_id=?");
            if (!$stmt) {
                echo json_encode(['error' => $conn->error]);
                exit;
            }
            $stmt->bind_param("sssssiii", $fname, $lname, $phone, $level, $age, $description, $id, $user_id);
        }
        if (!$stmt->execute()) {
            echo json_encode(['error' => $stmt->error]);
            exit;
        }
        echo json_encode([
            'message' => $stmt->affected_rows > 0 ? '✅ اطلاعات با موفقیت ویرایش شد' : '⚠️ تغییری صورت نگرفت یا مجاز نیستید'
        ]);
        exit;
    } else {
        $stmt = $conn->prepare("INSERT INTO user_data (user_id, firstname, lastname, phone, level, age, description, registered) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            echo json_encode(['error' => $conn->error]);
            exit;
        }
        $stmt->bind_param("issssisi", $user_id, $fname, $lname, $phone, $level, $age, $description, $registered);
        if (!$stmt->execute()) {
            echo json_encode(['error' => $stmt->error]);
            exit;
        }
        echo json_encode([
            'message' => $stmt->affected_rows > 0 ? '✅ اطلاعات با موفقیت ثبت شد' : '❌ خطا در ثبت اطلاعات'
        ]);
        exit;
    }
}

// درخواست نامعتبر
echo json_encode(['message' => 'درخواست نامعتبر']);
exit;
