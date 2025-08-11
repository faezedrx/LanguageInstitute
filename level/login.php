<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fa">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['"Vazirmatn"', 'sans-serif'],
          },
        }
      }
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet">
  <title>ورود</title>
</head>
<body class="bg-gradient-to-br from-blue-200 via-white to-red-200 min-h-screen flex items-center justify-center font-sans p-4">

  <div class="bg-white/90 backdrop-blur-md shadow-lg rounded-xl px-8 py-10 w-full max-w-md">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">🔐 ورود به سیستم</h2>

    <form action="process_login.php" method="post" class="space-y-4">
      <div>
        <label class="block text-sm text-gray-600 mb-1">نام کاربری</label>
        <div class="relative">
          <input type="text" name="username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"/>
          <div class="absolute left-3 top-2.5 text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A9 9 0 0112 15c2.48 0 4.713.99 6.364 2.596M15 11a3 3 0 10-6 0 3 3 0 006 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div>
        <label class="block text-sm text-gray-600 mb-1">رمز عبور</label>
        <div class="relative">
          <input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"/>
          <div class="absolute left-3 top-2.5 text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.38 0 2.5-1.12 2.5-2.5S13.38 6 12 6s-2.5 1.12-2.5 2.5S10.62 11 12 11zm0 1c-2.21 0-4 1.79-4 4h8c0-2.21-1.79-4-4-4z" />
            </svg>
          </div>
        </div>
      </div>

      <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition flex justify-center items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m0 0l4-4m-4 4l4 4m16 0V4a2 2 0 00-2-2H7a2 2 0 00-2 2v16a2 2 0 002 2h10a2 2 0 002-2z" />
        </svg>
        ورود
      </button>
    </form>
  </div>
</body>
</html>
