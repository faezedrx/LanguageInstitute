<?php
// فایل کامل: فرم محاسبه پیمنت — نسخه اصلاح‌شده
// تغییرات: 
// 1) سطرهایی که ستون 'سطح' دارند و مقدار 'سطح' خالی است، نه ذخیره می‌شوند و نه نمایش داده می‌شوند
// 2) شماره حساب / کارت / شبا به‌صورت کامل ذخیره و نمایش داده می‌شوند (fixNumberString غیرفعال)
// 3) پاپ‌آپ نمایش ترم قبل بازطراحی و با انیمیشن شده است

// ----------------------------
// پیکربندی اولیه
// ----------------------------
$username = $_GET['username'] ?? 'guest';
$username_safe = preg_replace('/[^\p{L}\p{N}_-]+/u', '_', $username);

$termFolder = __DIR__ . "/زمستان1";
$csvFile = $termFolder . "/{$username_safe}.csv";
$legacyCsvFile = __DIR__ . "/{$username_safe}.csv";

$curlUrl = "https://httpbin.org/post";

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// شماره‌ها بدون تغییر بازگردانده می‌شوند (نمایش کامل)
function fixNumberString($v) { return $v; }

// تعریف جدول‌ها
$tables = [
    'public' => ['title'=>'کلاس‌های عمومی','cols'=>['کد کلاس','سطح','تعداد زبان‌آموزان','تعداد جلسات برگزار شده','تاریخ شروع','تاریخ پایان','توضیحات'],'types'=>['text','text','number','number','text','text','text']],
    'subGot' => ['title'=>'تعداد جلساتی که در این ترم ساب گرفتید','cols'=>['تاریخ','روز','سطح','نام استاد جایگزین شما'],'types'=>['text','text','text','text']],
    'subWent' => ['title'=>'تعداد جلساتی که در این ترم ساب رفتید','cols'=>['تاریخ','روز','سطح','نام استاد اصلی'],'types'=>['text','text','text','text']],
    'extra' => ['title'=>'فوق برنامه‌ها','cols'=>['برنامه','تعداد جلسات برگزار شده','تاریخ','توضیحات'],'types'=>['text','number','text','text']],
    'private' => ['title' => 'کلاس‌های خصوصی','cols'  => ['نام زبان آموز','تاریخ برگزاری کلاس','تعداد جلسات'],'types' => ['text','text','number']],
    'makeup' => ['title'=>'کلاس‌های جبرانی یا چرخشی','cols'=>['نام زبان آموز','تاریخ برگزاری','مدت زمان کلاس','تعداد جلسات'],'types'=>['text','text','text','number']],
    'call' => ['title'=>'تماس پشتیبانی','cols'=>['کد کلاس','تعداد تماس موفق','جمع مدت زمان تماس','توضیحات'],'types'=>['text','number','text','text']],
    'supervisor' => ['title'=>'ویژه اساتید ناظر طرح هوم چارج','cols'=>['تاریخ حضور','ساعت حضور','توضیحات'],'types'=>['text','text','text']],
];

$message = '';
$error = '';
$loadedData = []; // برای پر کردن اولیه (در صورت وجود فایل قبلی)

// فیلدهای اصلی (برای فرم بالا)
$fields = [
    'نام_استاد' => 'نام استاد',
    'مبلغ_پیمنت' => 'مبلغ پیمنت',
    'شماره_حساب' => 'شماره حساب',
    'شماره_کارت' => 'شماره کارت',
    'شماره_شبا' => 'شماره شبا'
];

// ----------------------------
// پاسخ AJAX برای نمایش ترم قبل
// ----------------------------
if (isset($_GET['action']) && $_GET['action'] === 'showPrev') {
    $prevText = file_exists($legacyCsvFile) ? file_get_contents($legacyCsvFile) : '';
    if (!is_dir($termFolder)) @mkdir($termFolder, 0777, true);
    if (!file_exists($csvFile)) {
        $mainFields = ['نام_استاد','مبلغ_پیمنت','شماره_حساب','شماره_کارت','شماره_شبا'];
        $mainRow = [$username, '', '', '', ''];
        $f = @fopen($csvFile, 'w');
        if ($f !== false) {
            fwrite($f, "\xEF\xBB\xBF");
            fputcsv($f, $mainFields);
            fputcsv($f, $mainRow);
            fclose($f);
        }
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'text' => $prevText], JSON_UNESCAPED_UNICODE);
    exit;
}

// ----------------------------
// بارگذاری فایل قبلی (در صورت وجود) — برای پیش‌پر کردن فیلدها (اختیاری)
// ----------------------------
$readCsvFile = null;
if (file_exists($csvFile)) {
    $readCsvFile = $csvFile;
} elseif (file_exists($legacyCsvFile)) {
    $readCsvFile = $legacyCsvFile;
}

if ($readCsvFile) {
    $rows = array_map('str_getcsv', file($readCsvFile));
    $rows = array_map(fn($r) => array_map('trim', $r), $rows);
    $section = null;
    foreach ($rows as $r) {
        if (empty(array_filter($r))) continue;
        foreach ($tables as $id => $t) {
            if ($r[0] === $t['title']) { $section = $id; continue 2; }
        }
        if ($section && isset($tables[$section])) {
            if (!isset($loadedData[$section])) {
                $loadedData[$section] = [];
                if ($r === $tables[$section]['cols']) continue;
            }
            $loadedData[$section][] = $r;
            continue;
        }
        if (!isset($loadedData['نام_استاد'])) {
            $r[0] = preg_replace('/^\x{FEFF}/u', '', $r[0]);
            $mainCols = ['نام_استاد','مبلغ_پیمنت','شماره_حساب','شماره_کارت','شماره_شبا'];
            if ($r === $mainCols) continue;
            $loadedData['نام_استاد']   = $r[0] ?? '';
            $loadedData['مبلغ_پیمنت']  = $r[1] ?? '';
            $loadedData['شماره_حساب']  = $r[2] ?? '';
            $loadedData['شماره_کارت']  = $r[3] ?? '';
            $loadedData['شماره_شبا']   = $r[4] ?? '';
            continue;
        }
    }
}

// آماده‌سازی valData برای پر کردن فیلدها (اگر بخواهید از ترم قبل استفاده شود)
$valData = [];
foreach ($fields as $k => $label) {
    $valData[$k] = $loadedData[$k] ?? '';
}

// ----------------------------
// ذخیره‌سازی POST
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userData = $_POST['data'] ?? [];
    $allRows = [];

    // ردیف اصلی
    $mainFields = array_keys($fields);
    $mainRow = [];
    foreach ($mainFields as $f) {
        $val = is_string($userData[$f] ?? '') ? trim($userData[$f]) : ($userData[$f] ?? '');
        if (in_array($f, ['شماره_حساب','شماره_کارت','شماره_شبا'])) {
            $val = fixNumberString($val); // الان همان مقدار بازگردانده می‌شود
        }
        $mainRow[] = $val;
    }
    if (array_filter($mainRow, fn($v)=>$v !== '')) {
        $allRows[] = $mainFields;
        $allRows[] = $mainRow;
    }

    // پردازش جداول — گروه‌بندی و فیلتر سطرهایی که سطح خالی دارند
    foreach ($tables as $id=>$t) {
        if (!empty($userData[$id]) && is_array($userData[$id])) {
            $cols = count($t['cols']);
            $flat = array_values($userData[$id]);
            $grouped = []; $tmp = [];
            foreach ($flat as $i => $cell) {
                $tmp[] = is_string($cell) ? trim($cell) : $cell;
                if ((($i+1) % $cols) === 0) { $grouped[] = $tmp; $tmp = []; }
            }
            if (!empty($tmp)) $grouped[] = $tmp;

            // فیلتر: اگر جدول ستون 'سطح' دارد، سطرهایی که سطح خالی دارند کنار گذاشته شوند
            $levelIndex = array_search('سطح', $t['cols']);
            $filtered = [];
            foreach ($grouped as $r) {
                if ($levelIndex !== false) {
                    $lvl = trim($r[$levelIndex] ?? '');
                    if ($lvl === '') continue; // حذف سطر
                }
                // اگر همه سلول‌ها خالی هستند هم نادیده بگیریم
                if (!array_filter($r, fn($v) => (string)$v !== '')) continue;
                $filtered[] = $r;
            }

            if (count($filtered)) {
                $allRows[] = [];
                $allRows[] = [$t['title']];
                $allRows[] = $t['cols'];
                foreach ($filtered as $rr) $allRows[] = $rr;
            }
        }
    }

    // ذخیره CSV با BOM
    try {
        if (!is_dir($termFolder)) {
            if (!mkdir($termFolder, 0777, true) && !is_dir($termFolder)) {
                throw new Exception('خطا در ایجاد پوشهٔ زمستان1 برای ذخیره فایل');
            }
        }
        $f = fopen($csvFile, 'w');
        if ($f === false) throw new Exception('خطا در ایجاد یا نوشتن فایل CSV');

        fwrite($f, "\xEF\xBB\xBF");
        foreach ($allRows as $r) {
            if (!is_array($r)) $r = [$r];
            fputcsv($f, $r);
        }
        fclose($f);

        // ارسال به سرور (اختیاری)
        $ch = curl_init($curlUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode([ 'user'=>$username_safe, 'rows'=>$allRows ], JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 10,
        ]);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($resp === false || ($http < 200 || $http >= 300)) {
            $error = "ذخیره انجام شد اما ارسال به سرور با مشکل مواجه شد (HTTP $http). $curlErr";
            $message = "ذخیره محلی انجام شد ";
        } else {
            $message = "ذخیره و ارسال با موفقیت انجام شد (HTTP $http)";
        }

        header("Location: ?username=" . urlencode($username));
        exit;

    } catch (Exception $ex) {
        $error = 'خطا: ' . $ex->getMessage();
    }
}

// مسیر دانلود (نسبی)
$csvRel = ltrim(str_replace('\\','/', substr($csvFile, strlen(__DIR__))), '/');
$prevRel = ltrim(str_replace('\\','/', "{$username_safe}.csv"), '/');

?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>فرم محاسبه پیمنت اساتید | <?= h($username) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://db.onlinewebfonts.com/c/3671adca6f650c92b83f906e49656986?family=B+Nazanin" rel="stylesheet">
<link href="https://db.onlinewebfonts.com/c/a0ea7e7833cd4f7694a4913fccb9aacf?family=B+Titr+Bold" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-date@1.0.6/dist/persian-date.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
<style>
body { font-family: 'B Nazanin', sans-serif; }
h1,h2,h3 { font-family: 'B Titr Bold', sans-serif; }
.card { background: linear-gradient(180deg, #ffffff, #fbfdff); }
input, textarea { transition: box-shadow .15s ease, transform .08s ease; }
input:focus, textarea:focus { box-shadow: 0 0 0 6px rgba(37,99,235,0.06); transform: translateY(-1px); }
.modal-backdrop { backdrop-filter: blur(6px); background: rgba(0,0,0,0.45); }
.modal-card { transform: scale(.95); opacity: 0; transition: all .25s ease; }
.modal-open .modal-card { transform: scale(1); opacity: 1; }
</style>
</head>
<body class="bg-gray-50">
<div class="max-w-6xl mx-auto p-6">
<h1 class="text-3xl text-center text-blue-700 font-extrabold mb-6">فرم محاسبه پیمنت ترم زمستان 1 1404</h1>
<?php if($message): ?><div class="mb-4 p-3 rounded-lg shadow text-green-800 bg-green-100"><?= h($message) ?></div><?php endif; ?>
<?php if($error): ?><div class="mb-4 p-3 rounded-lg shadow text-red-800 bg-red-100"><?= h($error) ?></div><?php endif; ?>

<form method="post" class="space-y-6 card p-6 rounded-2xl shadow">
<section class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
<?php foreach($fields as $name=>$label): ?>
<div class="flex flex-col">
  <label class="text-sm font-semibold mb-1 text-gray-700"><?= h($label) ?></label>
  <input type="text"
         name="data[<?= $name ?>]"
         value="<?= h($valData[$name] ?? ($name=='نام_استاد' ? $username : '')) ?>"
         placeholder="<?= h($label) ?>"
         class="border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition shadow-sm">
</div>
<?php endforeach; ?>
</section>

<section class="space-y-4">
<?php
// قبل از رندر: برای هر جدول اگر ستون 'سطح' داشته باشد، ردیف‌هایی که سطح خالی دارند حذف شوند.
// اگر پس از فیلتر هیچ ردیفی باقی نماند، کل بخش رندر نشود.
foreach($tables as $id=>$t):
    $rows = $loadedData[$id] ?? [[]];
    // پاکسازی سطرها از ردیف‌های خالی یا ردیف‌هایی که سطحشان خالی است
    $cleanRows = [];
    $levelIndex = array_search('سطح', $t['cols']);
    foreach ($rows as $r) {
        if (!is_array($r)) continue;
        // اگر همه خالی‌اند رد کن
        if (!array_filter($r, fn($v) => (string)$v !== '')) continue;
        if ($levelIndex !== false) {
            $lvl = trim($r[$levelIndex] ?? '');
            if ($lvl === '') continue;
        }
        $cleanRows[] = $r;
    }
    // اگر ردیفی نداریم، یک ردیف خالی پیش‌فرض نشان دهیم (برای وارد کردن داده جدید)
    if (empty($cleanRows)) {
        $cleanRows = [array_fill(0, count($t['cols']), '')];
    }
    // اگر جدول بعد از فیلتر هیچ اطلاعات ذخیره‌شده‌ای نداشت و تنها ردیف پیش‌فرض خالی است،
    // باز هم بخش نمایش داده شود تا کاربر بتواند سطر اضافه کند. اما اگر شما می‌خواهید بخش‌های بدون دیتای ذخیره‌شده کلاً پنهان باشند،
    // این شرط را به: if (empty($loadedData[$id])) continue; تغییر دهید.
?>
<div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
<div class="flex items-center justify-between mb-3">
<div class="flex items-center gap-3">
<h2 class="font-bold text-gray-700"><?= h($t['title']) ?></h2>
<span class="text-xs text-gray-400">(<?= count($t['cols']) ?> ستون)</span>
</div>
<div class="flex items-center gap-2">
<button type="button" data-table="<?= h($id) ?>" class="addRow inline-flex items-center gap-2 bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">+ افزودن سطر</button>
</div>
</div>
<div class="overflow-x-auto w-full max-w-full">
<table class="min-w-full text-sm table-auto border-collapse" id="<?= h($id) ?>Table">
<thead class="bg-blue-50"><tr>
<?php foreach($t['cols'] as $c): ?><th class="border px-3 py-2 text-right"><?= h($c) ?></th><?php endforeach; ?><th class="border px-3 py-2 text-center">اقدام</th>
</tr></thead>
<tbody>
<?php
foreach ($cleanRows as $row):
?>
<tr class="hover:bg-gray-50">
<?php foreach($t['cols'] as $i => $c): ?>
<td class="border p-1">
  <input type="<?= $t['types'][$i] ?? 'text' ?>" name="data[<?= h($id) ?>][]" value="<?= h($row[$i] ?? '') ?>" class="w-full border border-gray-200 p-1 rounded <?= $t['types'][$i]=='date'?'datepicker':'' ?>" placeholder="<?= h($c) ?>">
</td>
<?php endforeach; ?>
<td class="border text-center p-1"><button type="button" class="removeRow bg-red-500 text-white px-2 py-1 rounded text-xs">حذف</button></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<?php endforeach; ?>
</section>

<section>
<label class="font-bold mb-1 block">توضیحات کلی</label>
<textarea name="data[توضیحات_کلی]" rows="3" class="w-full border border-gray-200 p-2 rounded-lg whitespace-pre-wrap resize-y" placeholder="هر توضیح اضافی را اینجا وارد کنید..."><?= h($valData['توضیحات_کلی'] ?? '') ?></textarea>
</section>

<div class="flex items-center gap-4">
<button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition inline-flex items-center gap-2">ذخیره و ارسال</button>

<button type="button" id="showLastTerm" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
    مشاهده ترم قبل
</button>

<a href="<?= h($csvRel) ?>" class="ml-auto text-sm text-gray-600 hover:text-gray-800">⬇️ <span class="underline">دانلود CSV</span></a>
</div>
</form>
</div>

<!-- POPUP زیباتر -->
<div id="lastTermModal" class="fixed inset-0 hidden justify-center items-center p-4 z-50 modal-backdrop">
  <div class="modal-card bg-white w-full max-w-3xl rounded-3xl shadow-2xl p-6 relative">
    <button id="closeLastTerm" class="absolute top-4 left-4 text-gray-400 hover:text-gray-700 text-3xl font-bold">×</button>
    <!--<h2 class="text-2xl font-extrabold text-blue-700 mb-4 text-center">اطلاعات ترم قبل</h2>-->
    <div id="lastTermContent" class="text-sm bg-gray-50 border border-gray-200 rounded-2xl p-4 max-h-96 overflow-y-auto shadow-inner leading-7">
    </div>
    <div class="text-center mt-6">
        <button id="closeLastTerm2" class="bg-red-600 text-white px-6 py-2 rounded-xl shadow hover:bg-red-700 transition">بستن</button>
    </div>
  </div>
</div>

<script>
(() => {
const tables = <?= json_encode(array_keys($tables), JSON_UNESCAPED_UNICODE) ?>;
document.querySelectorAll('.addRow').forEach(btn => {
    btn.addEventListener('click', e => {
        const id = e.currentTarget.getAttribute('data-table');
        const tbody = document.querySelector('#' + id + 'Table tbody');
        if (!tbody) return;
        let first = tbody.querySelector('tr:first-child');
        let clone;
        if(first){
            clone = first.cloneNode(true);
            clone.querySelectorAll('input').forEach(i=>i.value='');
        }else{
            const cols = document.querySelectorAll('#'+id+'Table thead th').length-1;
            clone = document.createElement('tr');
            clone.className='hover:bg-gray-50';
            for(let i=0;i<cols;i++){
                const td=document.createElement('td'); td.className='border p-1';
                const input=document.createElement('input'); input.type='text'; input.name=`data[${id}][]`; input.className='w-full border border-gray-200 p-1 rounded'; td.appendChild(input); clone.appendChild(td);
            }
            const tdAction=document.createElement('td'); tdAction.className='border text-center p-1'; tdAction.innerHTML='<button type="button" class="removeRow bg-red-500 text-white px-2 py-1 rounded text-xs">حذف</button>'; clone.appendChild(tdAction);
        }
        clone.style.opacity=0; tbody.appendChild(clone); requestAnimationFrame(()=>clone.style.opacity=1);
    });
});
document.addEventListener('click', e => {
    if(e.target.classList.contains('removeRow')){
        if(!confirm('آیا از حذف این سطر مطمئن هستید؟')) return;
        const tr=e.target.closest('tr');
        tr.style.transition='opacity .2s, transform .2s';
        tr.style.opacity=0;
        tr.style.transform='translateY(-6px)';
        setTimeout(()=>tr.remove(),220);
    }
});

// datepicker init
document.addEventListener('DOMContentLoaded', () => {
  $('input.datepicker').persianDatepicker({
    format: 'YYYY/MM/DD',
    autoClose: true,
    observer: true,
    initialValue: false,
    calendar: { persian: true },
    navigator: { enabled: true, scroll: { enabled: true } },
    toolbox: { enabled: true, calendarSwitch: { enabled: true }, text: { btnToday: 'امروز' } },
    onlySelect: 'day',
    onSelect: function(unix) {
      $(this).val(new persianDate(unix).format('YYYY/MM/DD'));
    }
  });
});

// last term popup logic (زیباتر)
const lastBtn = document.getElementById('showLastTerm');
const modal = document.getElementById('lastTermModal');
const closeBtns = [document.getElementById('closeLastTerm'), document.getElementById('closeLastTerm2')];
const lastContent = document.getElementById('lastTermContent');

lastBtn.addEventListener('click', async () => {
    modal.classList.remove('hidden');
    setTimeout(()=> modal.querySelector('.modal-card').classList.add('scale-100','opacity-100'), 20);
    lastContent.innerHTML = "<div class='text-center text-gray-500 py-4'>در حال بارگذاری...</div>";
    try {
        const res = await fetch(`?username=<?= urlencode($username) ?>&action=showPrev`);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        if (!data.ok || !data.text.trim()) {
            lastContent.innerHTML = "<p class='text-gray-500 text-center'>ترم قبل خالی است.</p>";
            return;
        }
        const lines = data.text.trim().split(/\r?\n/).filter(l=>l.trim()!=='');
        if (lines.length === 0) {
            lastContent.innerHTML = "<p class='text-gray-500 text-center'>ترم قبل خالی است.</p>";
            return;
        }

        // ساخت جدول خواناتر: اگر خط تنها یک ستون داشت آن را سطر ساده نشان می‌دهیم
        let html = '<div class=\"overflow-x-auto\"><table class=\"w-full text-sm border border-gray-300 rounded-xl overflow-hidden\"><tbody>';
        lines.forEach((line, i) => {
            // ساده‌ترین تقسیم بر حسب کاما (قابل بهبود برای CSV پیچیده)
            const cols = line.split(',').map(c => c.replace(/^\"|\"$/g, '').trim());
            html += `<tr class="${i%2? 'bg-gray-50':''}">`;
            cols.forEach(col => {
                html += `<td class="border px-3 py-1 text-sm">${col || '&nbsp;'}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody></table></div>';
        lastContent.innerHTML = html;
    } catch (err) {
        console.error(err);
        lastContent.innerHTML = "<p class='text-red-500 text-center'>خطا در بارگذاری ترم قبل.</p>";
    }
});

closeBtns.forEach(btn =>
    btn.addEventListener('click', () => {
        modal.querySelector('.modal-card').classList.remove('scale-100','opacity-100');
        setTimeout(() => modal.classList.add('hidden'), 250);
    })
);
})();
</script>
</body>
</html>
