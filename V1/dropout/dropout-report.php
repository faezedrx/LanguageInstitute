<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username']; // مثلا: katayoun_hassan_gholi_zadeh

$parts = explode('_', $username);

if (count($parts) < 2) {
    echo "<p class='text-center mt-8 text-red-600'>نام کاربری معتبر نیست و بخش جستجو ندارد.</p>";
    exit;
}

$csvFile = __DIR__ . "/data.CSV";

if (!file_exists($csvFile)) {
    die("فایل داده‌ها یافت نشد.");
}

$file = fopen($csvFile, "r");
$header = fgetcsv($file);

$data = [];

while (($row = fgetcsv($file)) !== false) {
    list($ostad, $sath, $term, $total, $drop) = $row;
    $total = (int)$total;
    $drop = (int)$drop;
    $rate = $total > 0 ? round(($drop / $total) * 100, 2) : 0;

    $data[] = [
        "ostad" => $ostad,
        "sath" => $sath,
        "term" => $term,
        "total" => $total,
        "drop" => $drop,
        "rate" => $rate,
    ];
}

fclose($file);

// تابع جستجو با بیشترین تعداد تطابق بخش‌ها
function searchWithParts(array $data, array $parts): ?string {
    $searchParts = array_slice($parts, 1); // بخش‌های یوزرنیم بعد از اول

    $matchedOstads = [];

    foreach ($data as $row) {
        // تقسیم نام استاد به بخش‌های جداگانه بر اساس فاصله یا _
        $ostadParts = preg_split('/[\s_]+/u', $row['ostad']);
        $matches = 0;

        foreach ($searchParts as $sp) {
            foreach ($ostadParts as $op) {
                if (mb_strtolower($op) === mb_strtolower($sp)) {
                    $matches++;
                    break;
                }
            }
        }

        if ($matches > 0) {
            $matchedOstads[$row['ostad']] = $matches;
        }
    }

    if (empty($matchedOstads)) {
        return null;
    }

    // استاد با بیشترین تعداد تطابق رو برگردون
    arsort($matchedOstads);
    return array_key_first($matchedOstads);
}

$matchedOstad = searchWithParts($data, $parts);

if (!$matchedOstad) {
    echo "<p class='text-center mt-8 text-red-600'>استادی با نام مشابه یافت نشد.</p>";
    exit;
}

$filteredData = array_filter($data, fn($row) => $row['ostad'] === $matchedOstad);

$rates = array_column($filteredData, 'rate');
$avgRate = count($rates) > 0 ? round(array_sum($rates) / count($rates), 2) : 0;
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>تحلیل ریزش استاد <?= htmlspecialchars($matchedOstad) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
  <style>
    @import url('https://cdn.fontcdn.ir/Font/Persian/IranYekan/IranYekan.css');
    body { font-family: 'IranYekan', sans-serif; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 p-6 min-h-screen">

  <div class="max-w-4xl mx-auto space-y-12">

    <h1 class="text-4xl font-extrabold text-center text-blue-700 mb-8 border-b pb-4">
      📊 تحلیل ریزش استاد <?= htmlspecialchars($matchedOstad) ?>
    </h1>

    <div class="bg-white border border-gray-200 rounded-xl shadow p-6">
      <h3 class="text-xl font-bold text-blue-700 mb-2"><?= htmlspecialchars($matchedOstad) ?></h3>
      <p class="text-sm text-gray-600">میانگین نرخ ریزش: <span class="text-blue-600 font-semibold"><?= $avgRate ?>%</span></p>

      <table class="w-full text-sm border mt-4">
        <thead>
          <tr class="bg-gray-100 text-gray-700 text-center">
            <th class="p-2">سطح</th><th class="p-2">ترم</th><th class="p-2">کل</th><th class="p-2">ریزش</th><th class="p-2">نرخ</th><th class="p-2">تحلیل</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($filteredData as $row):
            $tag = ($row['rate'] < $avgRate - 10) ? 'قوی' : (($row['rate'] > $avgRate + 10) ? 'ضعیف' : 'نرمال');
          ?>
          <tr class="text-center">
            <td class="p-2"><?= htmlspecialchars($row['sath']) ?></td>
            <td class="p-2"><?= htmlspecialchars($row['term']) ?></td>
            <td class="p-2"><?= $row['total'] ?></td>
            <td class="p-2"><?= $row['drop'] ?></td>
            <td class="p-2"><?= $row['rate'] ?>%</td>
            <td class="p-2"><?= $tag ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="mt-6">
        <h4 class="text-md font-bold text-purple-700 mb-2">📊 نمودار نرخ ریزش برای استاد <?= htmlspecialchars($matchedOstad) ?></h4>
        <div id="chart" class="w-full h-72"></div>
      </div>
    </div>

  </div>

  <script>
    const terms = <?= json_encode(array_map(fn($r)=>$r['term'].' - '.$r['sath'], $filteredData), JSON_UNESCAPED_UNICODE) ?>;
    const rates = <?= json_encode(array_column($filteredData, 'rate')) ?>;

    Plotly.newPlot('chart', [{
      x: terms,
      y: rates,
      type: 'bar',
      marker: { color: 'rgba(37, 99, 235, 0.8)' },
      text: rates,
      textposition: 'auto',
    }], {
      margin: { t: 30, r: 20, l: 30, b: 80 },
      yaxis: { title: 'نرخ ریزش (%)' },
      font: { family: 'IranYekan, sans-serif' },
      plot_bgcolor: '#fff',
      paper_bgcolor: '#fff',
    }, {responsive: true});
  </script>

</body>
</html>
