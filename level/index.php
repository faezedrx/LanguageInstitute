<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "db.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// دریافت id از آدرس
$level_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$userId = $_SESSION['user_id'];
$userId = $level_id;
$isAdmin = ($_SESSION['username'] ?? '') === 'admin';

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <title>✨ ثبت اطلاعات کاربر ✨</title>
</head>
<body class="bg-gradient-to-br from-blue-100 via-white to-pink-100 min-h-screen p-6 font-sans">
  <div class="max-w-2xl mx-auto bg-white p-6 rounded-2xl shadow-2xl border border-blue-200">
    <h1 class="text-3xl font-extrabold text-center text-blue-700 mb-6">📝 ثبت اطلاعات کاربر</h1>
    
    <input type="hidden" name="level_id" value="<?= htmlspecialchars($level_id) ?>" />


    <form id="dataForm" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input name="firstname" placeholder="👤 نام" required class="w-full px-4 py-2 border border-blue-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
        <input name="lastname" placeholder="👥 نام خانوادگی" required class="w-full px-4 py-2 border border-blue-300 rounded-lg shadow-sm" />
        <input name="phone" placeholder="📱 شماره تماس" required class="w-full px-4 py-2 border border-blue-300 rounded-lg shadow-sm" inputmode="numeric" pattern="[0-9]*" />
        <input name="level" placeholder="📘 سطح" required class="w-full px-4 py-2 border border-blue-300 rounded-lg shadow-sm" />
        <input name="age" placeholder="🎂 سن" type="number" min="1" max="120" required class="w-full px-4 py-2 border border-blue-300 rounded-lg shadow-sm" />
      </div>

      <?php if ($isAdmin): ?>
        <div class="flex items-center gap-2">
          <input type="checkbox" name="registered" id="registeredCheckbox" class="h-4 w-4" />
          <label for="registeredCheckbox" class="select-none">✅ ثبت‌نام شده</label>
        </div>
      <?php endif; ?>

      <textarea name="description" placeholder="🗒️ توضیحات (اختیاری)" class="w-full px-4 py-2 border border-blue-300 rounded-lg shadow-sm resize-none h-24"></textarea>

      <input type="hidden" name="id" id="hiddenId" />
      <div class="flex gap-3">
        <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-lg font-semibold hover:bg-green-600 transition-all duration-200 shadow-md">
          🚀 ثبت اطلاعات
        </button>
        <button type="button" onclick="cancelEdit()" class="bg-gray-300 text-gray-700 px-4 rounded-lg font-medium hover:bg-gray-400 transition hidden" id="cancelEditBtn">
          ❌ انصراف
        </button>
      </div>
    </form>

    <div class="mt-6 flex gap-3 items-center">
      <input id="searchInput" type="text" placeholder="🔍 جستجو در نام، نام خانوادگی یا سطح..." class="flex-1 px-4 py-2 border border-purple-300 rounded-lg shadow-sm" />
      <button id="searchBtn" class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition shadow">جستجو</button>
    </div>

    <div id="result" class="mt-4 text-center text-green-600 font-semibold"></div>

    <h2 class="text-xl font-bold mt-8 mb-2 text-gray-700 border-b border-dashed pb-2">📋 لیست اطلاعات ثبت‌شده:</h2>
    <div id="dataList" class="space-y-3 text-sm"></div>
    <div id="pagination" class="mt-4 flex justify-center gap-2"></div>

    <div class="text-center mt-8">
      <a href="logout.php" class="text-red-500 hover:text-red-600 font-medium text-sm">🚪 خروج از حساب</a>
    </div>
  </div>

  <script>
    const form = document.getElementById('dataForm');
    const result = document.getElementById('result');
    const dataList = document.getElementById('dataList');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const hiddenId = document.getElementById('hiddenId');
    const registeredCheckbox = document.getElementById('registeredCheckbox');
    const paginationDiv = document.getElementById('pagination');

    const currentUserId = <?php echo $userId; ?>;
    const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;

    let currentPage = 1;
    const recordsPerPage = 7;

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(form);
      fetch('save_data.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.duplicate) {
          const d = data.duplicate;
          Swal.fire({
            title: '⚠️ نام و نام خانوادگی تکراری!',
            icon: 'warning',
            html: `
              <p>یک رکورد با این نام و نام خانوادگی قبلاً ثبت شده:</p>
              <ul style="text-align: right; direction: rtl; list-style: none; padding: 0;">
                <li><strong>نام:</strong> ${d.firstname}</li>
                <li><strong>نام خانوادگی:</strong> ${d.lastname}</li>
                <li><strong>شماره تماس:</strong> ${d.phone}</li>
                <li><strong>سطح:</strong> ${d.level}</li>
                <li><strong>سن:</strong> ${d.age}</li>
                <li><strong>ثبت‌نام شده:</strong> ${d.registered == 1 ? '✅ بله' : '❌ خیر'}</li>
                <li><strong>توضیحات:</strong> ${d.description || '-'}</li>
                <li><strong>ثبت توسط:</strong> ${d.username || '---'}</li>
              </ul>
              <p>اگر می‌خواهید اطلاعات قبلی را ویرایش کنید، می‌توانید از دکمه ویرایش در لیست استفاده کنید.</p>
              <p>در غیر اینصورت، می‌توانید ثبت اطلاعات جدید را ادامه دهید.</p>
            `,
            confirmButtonText: 'فهمیدم',
          });
        } else {
          result.textContent = data.message;
          form.reset();
          hiddenId.value = '';
          if(registeredCheckbox) registeredCheckbox.checked = false;
          cancelEditBtn.classList.add('hidden');
          loadData(searchInput.value.trim(), 1);
        }
      })
      .catch(() => {
        result.textContent = '❌ خطا در ارسال اطلاعات';
      });
    });

    searchBtn.addEventListener('click', () => {
      loadData(searchInput.value.trim(), 1);
    });

    function loadData(search = '', page = 1) {
      currentPage = page;
      let url = `save_data.php?action=list&limit=${recordsPerPage}&page=${page}`;
      if (search !== '') {
        url += '&search=' + encodeURIComponent(search);
      }
      fetch(url)
        .then(res => res.json())
        .then(data => {
          dataList.innerHTML = '';
          if (!data.rows.length) {
            dataList.innerHTML = '<div class="text-center text-gray-400">😕 موردی یافت نشد.</div>';
            paginationDiv.innerHTML = '';
            return;
          }
          data.rows.forEach(row => {
            const isOwner = row.user_id == currentUserId || isAdmin;
            dataList.innerHTML += `
              <div class="border border-gray-300 rounded-xl p-3 bg-gradient-to-r from-white to-blue-50 shadow-sm hover:shadow-md transition-all duration-200">
                <div class="text-lg font-semibold text-blue-800">👤 ${row.firstname} ${row.lastname}</div>
                <div class="text-gray-700">
                  📞 ${row.phone} | 📘 سطح: ${row.level} | 🎂 سن: ${row.age} 
                  ${row.registered == 1 ? `<span class="text-green-600 font-semibold mr-1">✅</span>` : ''}
                </div>
                ${row.description ? `<div class="mt-1 text-gray-600">🗒️ توضیحات: ${row.description}</div>` : ''}
                <div class="text-xs text-gray-400 mt-1">
                  👤 ثبت توسط: ${row.username || '---'} | 🕒 زمان: ${row.created_at}
                </div>
                ${isOwner ? `
                  <div class="mt-2 flex gap-2 text-xs">
                    <button onclick="editData(${row.id})" class="text-blue-500 hover:text-blue-700">✏️ ویرایش</button>
                    <button onclick="deleteData(${row.id})" class="text-red-500 hover:text-red-700">🗑️ حذف</button>
                  </div>
                ` : ''}
              </div>
            `;
          });
          renderPagination(data.totalPages);
        });
    }

    function renderPagination(totalPages) {
      paginationDiv.innerHTML = '';
      if (totalPages <= 1) return;

      for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = `px-3 py-1 rounded ${i === currentPage ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300'}`;
        btn.onclick = () => loadData(searchInput.value.trim(), i);
        paginationDiv.appendChild(btn);
      }
    }

    function editData(id) {
      fetch(`save_data.php?action=get&id=${id}`)
        .then(res => res.json())
        .then(data => {
          if (data.error) {
            result.textContent = '❌ رکورد یافت نشد یا دسترسی ندارید';
            return;
          }
          form.firstname.value = data.firstname;
          form.lastname.value = data.lastname;
          form.phone.value = data.phone;
          form.level.value = data.level;
          form.age.value = data.age;
          form.description.value = data.description || '';
          hiddenId.value = data.id;
          if(isAdmin && registeredCheckbox) {
            registeredCheckbox.checked = data.registered == 1;
          }
          result.textContent = '✏️ حالت ویرایش فعال است';
          cancelEditBtn.classList.remove('hidden');
        });
    }

    function deleteData(id) {
      if (!confirm("آیا از حذف مطمئنی؟")) return;
      fetch(`save_data.php?action=delete&id=${id}`)
        .then(res => res.json())
        .then(data => {
          result.textContent = data.message;
          loadData(searchInput.value.trim(), currentPage);
        });
    }

    function cancelEdit() {
      form.reset();
      hiddenId.value = '';
      if(registeredCheckbox) registeredCheckbox.checked = false;
      cancelEditBtn.classList.add('hidden');
      result.textContent = 'ویرایش لغو شد';
    }

    loadData();
  </script>
</body>
</html>
