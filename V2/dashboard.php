<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_set_cookie_params([
    'path' => '/',
    'httponly' => true,
    'secure' => false,
    'samesite' => 'Lax'
]);
session_start();

require_once __DIR__ . '/evaluation/db.php';
$mysqli->set_charset("utf8mb4");

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

if ($role === 'teach') {
    $u = $mysqli->real_escape_string($username);
    $res = $mysqli->query("SELECT id FROM teachers WHERE username='$u'");
    if ($res && $res->num_rows) {
        $teacher_id = $res->fetch_assoc()['id'];
    }
}

switch ($role) {
    case 'edu':    $evalLink = '/evaluation/index0.php'; break;
    case 'office': $evalLink = '/evaluation/index.php'; break;
    case 'teach':  $evalLink = "/evaluation/teacher-report.php?id={$teacher_id}"; break;
    default:       $evalLink = '#';
}

switch ($user_id) {
    case 51: $levelLink = '/level/index.php?id=3'; break;
    case 1:  $levelLink = '/level/index.php?id=4'; break;
    case 2:  $levelLink = '/level/index.php?id=2'; break;
    case 50: $levelLink = '/level/index.php?id=5'; break;
    case 48: $levelLink = '/level/index.php?id=1'; break;
    default: $levelLink = '/level/index.php';
}

$specialUsers = [51,1,2,50];
$showLevelCard = in_array($role,['office']) || in_array($user_id,$specialUsers);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>داشبورد | <?= htmlspecialchars($username) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{box-sizing:border-box;font-family:sans-serif}
body{
    margin:0;
    min-height:100vh;
    background:linear-gradient(135deg,#eef2ff,#fdf2f8);
    color:#333;
}
header{
    background:#fff;
    padding:16px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 10px rgba(0,0,0,.1);
    position:sticky;
    top:0;
    z-index:10;
}
header h1{
    margin:0;
    font-size:22px;
    color:#4338ca;
    display:flex;
    align-items:center;
    gap:8px;
}
header .user{
    display:flex;
    align-items:center;
    gap:12px;
    font-size:14px;
}
.logout{
    background:#fee2e2;
    color:#b91c1c;
    padding:6px 12px;
    border-radius:8px;
    text-decoration:none;
    transition:.3s;
}
.logout:hover{background:#fecaca}

main{
    padding:24px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:20px;
    animation:fadeIn .6s ease forwards;
}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}

.card{
    background:#fff;
    border-radius:20px;
    padding:24px;
    box-shadow:0 8px 20px rgba(0,0,0,.1);
    text-decoration:none;
    color:inherit;
    transition:.3s;
}
.card:hover{
    transform:translateY(-6px);
    box-shadow:0 16px 30px rgba(0,0,0,.15);
}

.card .icon{
    font-size:28px;
    margin-bottom:12px;
}
.card h2{
    margin:0 0 8px;
    font-size:20px;
}
.card p{
    margin:0;
    font-size:14px;
    color:#666;
}

.indigo{color:#4f46e5}
.yellow{color:#ca8a04}
.green{color:#15803d}
.red{color:#b91c1c}
</style>
</head>

<body>

<header>
    <h1>📊 داشبورد</h1>
    <div class="user">
        <span>سلام، <strong><?= htmlspecialchars($username) ?></strong></span>
        <a class="logout" href="logout.php">خروج</a>
    </div>
</header>

<main>

<?php if (in_array($role,['teach','office','edu'])): ?>
<a class="card" href="change_password.php">
    <div class="icon indigo">🔒</div>
    <h2>تغییر رمز عبور</h2>
    <p>به‌روزرسانی رمز عبور حساب</p>
</a>
<?php endif; ?>

<!--<a class="card" href="/finance/index.php?username=<?= urlencode($username) ?>">-->
<!--    <div class="icon yellow">💰</div>-->
<!--    <h2>مالی</h2>-->
<!--    <p>مدیریت دریافتی‌ها و گزارش‌ها</p>-->
<!--</a>-->

<?php if (in_array($role,['teach','office','edu'])): ?>
<a class="card" href="<?= $evalLink ?>">
    <div class="icon indigo">📈</div>
    <h2>نظرسنجی</h2>
    <p>مشاهده و مدیریت نظرسنجی‌ها</p>
</a>
<?php endif; ?>

<?php if ($role==='teach' && $_SESSION['username']!=='سهیل_حسین_زاده'): ?>
<a class="card" href="/dropout/dropout-report.php">
    <div class="icon red">➖</div>
    <h2>ریزشی‌ها</h2>
    <p>گزارش زبان‌آموزان ریزشی</p>
</a>
<?php endif; ?>

<?php if (in_array($role,['office','edu'])): ?>
<a class="card" href="/dropout">
    <div class="icon red">👥</div>
    <h2>ریزشی‌ها</h2>
    <p>مدیریت دانشجویان ریزشی</p>
</a>
<?php endif; ?>

<!--<?php if ($showLevelCard): ?>-->
<!--<a class="card" href="<?= htmlspecialchars($levelLink) ?>">-->
<!--    <div class="icon indigo">🔍</div>-->
<!--    <h2>تعیین سطح</h2>-->
<!--    <p>ثبت و مشاهده تعیین سطح</p>-->
<!--</a>-->
<!--<?php endif; ?>-->

<!--<?php if (in_array($role,['teach','office','edu'])): ?>-->
<!--<a class="card" href="/contact-manager.php">-->
<!--    <div class="icon green">🎧</div>-->
<!--    <h2>ارتباط با مدیر آموزش</h2>-->
<!--    <p>ارسال پیام مستقیم</p>-->
<!--</a>-->
<!--<?php endif; ?>-->

</main>

</body>
</html>