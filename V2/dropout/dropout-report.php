<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
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
        "ostad" => trim($ostad),
        "sath" => trim($sath),
        "term" => trim($term),
        "total" => $total,
        "drop" => $drop,
        "rate" => $rate,
    ];
}
fclose($file);

function searchWithParts(array $data, array $parts): ?string {
    $searchParts = array_slice($parts, 1);
    $searchFull = implode(' ', $searchParts);
    $allOstads = array_unique(array_column($data, 'ostad'));

    foreach ($allOstads as $ostad) {
        if (mb_strtolower(trim($ostad)) === mb_strtolower(trim($searchFull))) {
            return $ostad;
        }
    }

    $matchedOstads = [];
    foreach ($allOstads as $ostad) {
        $ostadParts = preg_split('/[\s_]+/u', $ostad);
        $matches = 0;
        foreach ($searchParts as $sp) {
            foreach ($ostadParts as $op) {
                if (mb_strtolower($op) === mb_strtolower($sp)) {
                    $matches++;
                    break;
                }
            }
        }
        if ($matches > 0) $matchedOstads[$ostad] = $matches;
    }

    if (empty($matchedOstads)) return null;
    arsort($matchedOstads);
    return array_key_first($matchedOstads);
}

$matchedOstad = searchWithParts($data, $parts);
if (!$matchedOstad) {
    echo "<p class='text-center mt-8 text-red-600'>استادی با نام مشابه یافت نشد.</p>";
    exit;
}

// داده‌های استاد جاری
$filteredData = array_filter($data, fn($row) => $row['ostad'] === $matchedOstad);
$rates = array_column($filteredData, 'rate');
$avgRate = count($rates) > 0 ? round(array_sum($rates) / count($rates), 2) : 0;

// رتبه‌بندی Top 3 استادان در هر سطحی که استاد جاری دارد
$levelsOfCurrent = array_unique(array_column($filteredData, 'sath'));
$levelStats = [];

foreach ($levelsOfCurrent as $sath) {
    // همه اساتید در این سطح
    $allInLevel = array_filter($data, fn($r) => $r['sath'] === $sath);

    // میانگین ریزش هر استاد در این سطح
    $ostadRates = [];
    foreach ($allInLevel as $r) {
        $ostadRates[$r['ostad']][] = $r['rate'];
    }

    $ostadAvgRates = [];
    foreach ($ostadRates as $ostad => $ratesArr) {
        $ostadAvgRates[$ostad] = round(array_sum($ratesArr) / count($ratesArr), 2);
    }

    // مرتب‌سازی از کمترین به بیشترین (درصد کمتر → رتبه بالاتر)
    asort($ostadAvgRates);

    // Top 3
    $levelStats[$sath] = array_slice($ostadAvgRates, 0, 3, true);
}
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
<div class="max-w-6xl mx-auto space-y-12">

  <h1 class="text-4xl font-extrabold text-center text-blue-700 mb-8 border-b pb-4">
    📊 تحلیل ریزش استاد <?= htmlspecialchars($matchedOstad) ?>
  </h1>

  <div class="bg-white border border-gray-200 rounded-xl shadow p-6">
    <h3 class="text-xl font-bold text-blue-700 mb-2"><?= htmlspecialchars($matchedOstad) ?></h3>
    <p class="text-sm text-gray-600">میانگین نرخ ریزش: 
      <span class="text-blue-600 font-semibold"><?= $avgRate ?>%</span>
    </p>

    <table class="w-full text-sm border mt-4">
      <thead>
        <tr class="bg-gray-100 text-gray-700 text-center">
          <th class="p-2">سطح</th>
          <th class="p-2">ترم</th>
          <th class="p-2">کل</th>
          <th class="p-2">ریزش</th>
          <th class="p-2">نرخ</th>
          <th class="p-2">تحلیل</th>
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
      <h4 class="text-md font-bold text-purple-700 mb-2">
        📊 نمودار نرخ ریزش برای استاد <?= htmlspecialchars($matchedOstad) ?>
      </h4>
      <div id="chart" class="w-full h-72"></div>
    </div>
  </div>

  <!-- رتبه‌بندی Top 3 هر سطح استاد جاری با مقایسه سایر اساتید -->
  <div class="bg-white border border-gray-200 rounded-xl shadow p-6">
    <h3 class="text-xl font-bold text-green-700 mb-4">🏆 رتبه‌بندی استاد در هر سطح (Top 3 با مقایسه)</h3>
    <table class="w-full text-sm border">
      <thead>
        <tr class="bg-gray-100 text-gray-700 text-center">
          <th class="p-2">رتبه</th>
          <th class="p-2">استاد</th>
          <th class="p-2">میانگین نرخ ریزش</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($levelStats as $sath => $ostads): ?>
        <tr class="bg-gray-100 text-gray-700 text-center">
            <td colspan="3" class="p-2 font-bold text-lg">سطح <?= htmlspecialchars($sath) ?></td>
        </tr>
        <?php $rank=1; foreach ($ostads as $ostad => $avg): ?>
        <tr class="text-center">
            <td class="p-2"><?= $rank++ ?></td>
            <td class="p-2"><?= htmlspecialchars($ostad) ?></td>
            <td class="p-2"><?= $avg ?>%</td>
        </tr>
        <?php endforeach; endforeach; ?>
      </tbody>
    </table>
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
  text: rates.map(r => r + '%'),
  textposition: 'outside',
  textfont: { size: 14 },
}], {
  margin: { t: 30, r: 20, l: 30, b: 80 },
  yaxis: { title: 'نرخ ریزش (%)', range: [0, Math.max(...rates)*1.3] },
  font: { family: 'IranYekan, sans-serif' },
  plot_bgcolor: '#fff',
  paper_bgcolor: '#fff',
}, {responsive: true});
</script>

</body>
</html>
