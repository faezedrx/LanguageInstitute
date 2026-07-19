<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>📊 عملکرد کلی اساتید در سطوح</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://cdn.fontcdn.ir/Font/Persian/IranYekan/IranYekan.css');
    body { font-family: 'IranYekan', sans-serif; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 p-6 min-h-screen">
  <div class="max-w-6xl mx-auto space-y-10">

    <h1 class="text-3xl font-extrabold text-center text-green-700 mb-10 border-b pb-4">
      👨‍🏫 عملکرد کلی اساتید در سطوح
    </h1>

    <?php
    // 📂 خواندن CSV
    $file = fopen("data.CSV", "r");
    $header = fgetcsv($file);

    $profLevels = []; // استاد ← سطح کلی ← مجموع کل و ریزش

    while (($row = fgetcsv($file)) !== false) {
        list($ostad, $sath, $term, $total, $drop) = $row;
        $total = (int)$total;
        $drop = (int)$drop;

        // 🔹 سطح کلی (حذف شماره انتهایی مثل 1،2،3...)
        $baseLevel = preg_replace('/\d+$/', '', $sath);

        if (!isset($profLevels[$ostad][$baseLevel])) {
            $profLevels[$ostad][$baseLevel] = ["total" => 0, "drop" => 0];
        }
        $profLevels[$ostad][$baseLevel]["total"] += $total;
        $profLevels[$ostad][$baseLevel]["drop"] += $drop;
    }
    fclose($file);

    // 📌 مرتب‌سازی اساتید الفبایی
    ksort($profLevels, SORT_STRING);

    // 📊 جدول خروجی
    echo "<div class='overflow-x-auto'>";
    echo "<table class='w-full border-collapse border border-gray-300 text-sm'>";
    echo "<thead class='bg-green-100 text-green-800'>";
    echo "<tr>
            <th class='border p-2 text-center'>#</th>
            <th class='border p-2 text-center'>استاد</th>
            <th class='border p-2 text-center'>سطح کلی</th>
            <th class='border p-2 text-center'>تعداد کل</th>
            <th class='border p-2 text-center'>تعداد ریزشی</th>
          </tr>";
    echo "</thead><tbody>";

    $counter = 1;
    foreach ($profLevels as $ostad => $levels) {
        ksort($levels, SORT_STRING); // سطح‌ها هم مرتب بشن
        foreach ($levels as $sath => $info) {
            echo "<tr class='hover:bg-gray-50 text-center'>
                    <td class='border p-2'>$counter</td>
                    <td class='border p-2 font-semibold text-blue-700'>$ostad</td>
                    <td class='border p-2 font-semibold'>$sath</td>
                    <td class='border p-2'>{$info['total']}</td>
                    <td class='border p-2 text-red-600 font-bold'>{$info['drop']}</td>
                  </tr>";
            $counter++;
        }
    }

    echo "</tbody></table>";
    echo "</div>";
    ?>
  </div>
</body>
</html>
