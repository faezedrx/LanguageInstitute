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
  <title>تحلیل ریزش اساتید</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
  <style>
    @import url('https://cdn.fontcdn.ir/Font/Persian/IranYekan/IranYekan.css');
    body { font-family: 'IranYekan', sans-serif; }
  </style>
  <script>
    function toggleModal(id) {
      const modal = document.getElementById(id);
      if (modal.classList.contains('hidden')) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden'; 
      } else {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = ''; 
      }
    }
  </script>
</head>
<body class="bg-gray-50 text-gray-800 p-6 min-h-screen">
  <div class="max-w-7xl mx-auto space-y-12">

    <h1 class="text-4xl font-extrabold text-center text-blue-700 mb-8 border-b pb-4">
      📊 تحلیل ریزش و رتبه‌بندی اساتید
    </h1>

    <?php
    $file = fopen("data.CSV", "r");
    $header = fgetcsv($file);

    $data = [];
    $profStats = [];

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

        if (!isset($profStats[$ostad])) {
            $profStats[$ostad] = ["rates" => [], "rows" => []];
        }
        $profStats[$ostad]["rates"][] = $rate;
        $profStats[$ostad]["rows"][] = count($data) - 1;
    }
    fclose($file);

    $profAvg = [];
    foreach ($profStats as $ostad => $info) {
        $avg = count($info["rates"]) > 0 ? array_sum($info["rates"]) / count($info["rates"]) : 0;
        $profAvg[$ostad] = round($avg, 2);
    }

    // ✅ کارت‌های اساتید (کلیک = باز شدن مودال)
    ksort($profAvg, SORT_STRING);
    echo "<div class='grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6'>";
    foreach ($profAvg as $ostad => $avg) {
        $id = "modal_" . md5($ostad);
        echo "<div class='bg-white border border-gray-200 rounded-xl shadow p-4 cursor-pointer hover:shadow-lg transition' onclick=\"toggleModal('$id')\">
                <h3 class='text-xl font-bold text-blue-700 mb-2'>$ostad</h3>
                <p class='text-sm text-gray-600'>میانگین ریزش: <span class='text-blue-600 font-semibold'>$avg%</span></p>
              </div>";
    }
    echo "</div>";

    // ✅ مودال جزئیات هر استاد
    foreach ($profStats as $ostad => $info) {
        $id = "modal_" . md5($ostad);
        $avg = $profAvg[$ostad];

        $totalSum = 0;
        $dropSum = 0;
        $terms = [];
        $rates = [];

        foreach ($info["rows"] as $i) {
            $totalSum += $data[$i]["total"];
            $dropSum += $data[$i]["drop"];
            $terms[] = $data[$i]["term"] . " - " . $data[$i]["sath"];
            $rates[] = $data[$i]["rate"];
        }

        echo "<div id='$id' class='fixed inset-0 hidden bg-black/50 z-50 items-center justify-center'>
                <div class='bg-white w-full max-w-3xl rounded-xl shadow-2xl p-6 space-y-6 max-h-[90vh] overflow-y-auto'>
                  <h2 class='text-xl font-bold text-blue-800 border-b pb-2'>تحلیل کامل استاد: $ostad</h2>

                  <div class='text-sm text-gray-700 space-y-2'>
                    <p>✅ <strong>میانگین نرخ ریزش:</strong> <span class='text-blue-700 font-semibold'>$avg%</span></p>
                    <p>👥 <strong>مجموع زبان‌آموزان:</strong> $totalSum</p>
                    <p>📉 <strong>مجموع ریزش‌ها:</strong> $dropSum</p>
                  </div>

                  <table class='w-full text-sm border mt-4'>
                    <thead>
                      <tr class='bg-gray-100 text-gray-700 text-center'>
                        <th class='p-2'>سطح</th><th class='p-2'>ترم</th><th class='p-2'>کل</th><th class='p-2'>ریزش</th><th class='p-2'>نرخ</th><th class='p-2'>تحلیل</th>
                      </tr>
                    </thead><tbody>";

        foreach ($info["rows"] as $i) {
            $rate = $data[$i]["rate"];
            $tag = ($rate < $avg - 10) ? 'قوی' : (($rate > $avg + 10) ? 'ضعیف' : 'نرمال');
            echo "<tr class='text-center'>
                    <td class='p-2'>{$data[$i]['sath']}</td>
                    <td class='p-2'>{$data[$i]['term']}</td>
                    <td class='p-2'>{$data[$i]['total']}</td>
                    <td class='p-2'>{$data[$i]['drop']}</td>
                    <td class='p-2'>{$data[$i]['rate']}%</td>
                    <td class='p-2'>$tag</td>
                  </tr>";
        }

        echo "</tbody></table>
              <div class='mt-6'>
                <h4 class='text-md font-bold text-purple-700 mb-2'>📊 نمودار نرخ ریزش برای استاد $ostad</h4>
                <div id='chart_$id' class='w-full h-72'></div>
              </div>
              <div class='text-center'>
                <button onclick=\"toggleModal('$id')\" class='mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition'>بستن</button>
              </div>
            </div>
          </div>";

        echo "<script>
        Plotly.newPlot('chart_$id', [{
          x: " . json_encode($terms, JSON_UNESCAPED_UNICODE) . ",
          y: " . json_encode($rates) . ",
          type: 'bar',
          marker: { color: 'rgba(37, 99, 235, 0.8)' },
          text: " . json_encode($rates) . ",
          textposition: 'auto',
        }], {
          margin: { t: 30, r: 20, l: 30, b: 80 },
          yaxis: { title: 'نرخ ریزش (%)' },
          font: { family: 'IranYekan, sans-serif' },
          plot_bgcolor: '#fff',
          paper_bgcolor: '#fff',
        }, {responsive: true});
        </script>";
    }

    // ✅ رتبه‌بندی اساتید بر اساس سطح
    $levelRanks = [];
    foreach ($data as $row) {
        $ostad = $row["ostad"];
        $sath = $row["sath"];
        $rate = $row["rate"];

        if (!isset($levelRanks[$sath][$ostad])) {
            $levelRanks[$sath][$ostad] = ["sum" => 0, "count" => 0];
        }
        $levelRanks[$sath][$ostad]["sum"] += $rate;
        $levelRanks[$sath][$ostad]["count"]++;
    }
    foreach ($levelRanks as $sath => &$profs) {
        foreach ($profs as $ostad => $info) {
            $profs[$ostad] = round($info["sum"] / $info["count"], 2);
        }
        asort($profs);
    }
    unset($profs);

    // ✅ گروه‌بندی سطح‌ها (مثل tn1..tn9 → tn)
    $groups = [];
    foreach ($levelRanks as $sath => $profs) {
        $base = preg_replace('/\d+$/', '', $sath);
        $groups[$base][] = $sath;
    }
    ?>

    <!-- ✅ انتخاب سطح -->
    <div class="mt-16">
      <h2 class="text-2xl font-bold text-green-700 mb-4">📌 رتبه‌بندی اساتید بر اساس سطح</h2>
      <select id="levelSelect" class="mb-6 p-2 border rounded">
        <option value="">-- انتخاب سطح --</option>
        <?php foreach ($groups as $base => $levels) {
            echo "<option value='$base'>$base</option>";
        } ?>
      </select>

      <?php 
      foreach ($groups as $base => $levels) {
          echo "<div class='levelGroup hidden' id='group_$base'>";
          foreach ($levels as $sath) {
              $profs = $levelRanks[$sath];
              echo "<div class='bg-green-50 border border-green-300 rounded-xl p-4 shadow-sm mb-4'>";
              echo "<h3 class='text-lg font-bold text-green-700 mb-3'>📚 سطح: $sath</h3>";
              echo "<ol class='list-decimal pr-5 space-y-1 text-sm text-gray-800'>";
              foreach ($profs as $ostad => $avgRate) {
                  echo "<li><span class='font-semibold'>$ostad</span> — میانگین ریزش: 
                        <span class='text-blue-700 font-bold'>$avgRate%</span></li>";
              }
              echo "</ol></div>";
          }
          echo "</div>";
      }
      ?>
    </div>

    <!-- ✅ انتخاب استاد -->
    <div class="mt-16">
      <h2 class="text-2xl font-bold text-purple-700 mb-4">📌 عملکرد اساتید در سطوح مختلف</h2>
      <select id="teacherSelect" class="mb-6 p-2 border rounded">
        <option value="">-- انتخاب استاد --</option>
        <?php foreach ($profAvg as $ostad => $avg) {
            echo "<option value='".md5($ostad)."'>$ostad</option>";
        } ?>
      </select>

      <?php 
      foreach ($profAvg as $ostad => $avg) {
          $ostadId = md5($ostad);
          $levelsForProf = [];
          foreach ($levelRanks as $sath => $profs) {
              if (isset($profs[$ostad])) {
                  $levelsForProf[$sath] = $profs[$ostad];
              }
          }
          asort($levelsForProf);

          echo "<div class='profGroup hidden' id='prof_$ostadId'>";
          echo "<div class='bg-blue-50 border border-blue-300 rounded-xl p-4 shadow-sm'>";
          echo "<h3 class='text-lg font-bold text-blue-700 mb-3'>👨‍🏫 استاد: $ostad</h3>";
          echo "<ol class='list-decimal pr-5 space-y-1 text-sm text-gray-800'>";
          foreach ($levelsForProf as $sath => $avgRate) {
              echo "<li><span class='font-semibold'>$sath</span> — میانگین ریزش: 
                    <span class='text-blue-700 font-bold'>$avgRate%</span></li>";
          }
          echo "</ol></div></div>";
      }
      ?>
    </div>

  </div>

  <script>
    // ✅ کنترل نمایش سطح‌ها
    document.getElementById('levelSelect').addEventListener('change', function() {
      document.querySelectorAll('.levelGroup').forEach(el => el.classList.add('hidden'));
      if (this.value) {
        document.getElementById('group_' + this.value).classList.remove('hidden');
      }
    });

    // ✅ کنترل نمایش استادها
    document.getElementById('teacherSelect').addEventListener('change', function() {
      document.querySelectorAll('.profGroup').forEach(el => el.classList.add('hidden'));
      if (this.value) {
        document.getElementById('prof_' + this.value).classList.remove('hidden');
      }
    });
  </script>
</body>
</html>
