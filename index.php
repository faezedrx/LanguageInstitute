<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title> آموزشگاه زبان</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap');

    /* Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      user-select: none;
    }

    body {
      font-family: 'Vazirmatn', sans-serif;
      background: linear-gradient(135deg, #001f3f, #ff3f3f);
      background-size: 200% 200%;
      animation: gradientFlow 15s ease infinite;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem 1.5rem;
      color: #fff;
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    @keyframes gradientFlow {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    h1 {
      font-size: 4rem;
      font-weight: 900;
      background: linear-gradient(90deg, #00c6ff, #ff3f3f);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
      animation: fadeInDown 1.8s ease forwards;
      margin-bottom: 3rem;
      letter-spacing: 0.07em;
      text-align: center;
      max-width: 100%;
      line-height: 1.1;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .nav-buttons {
      display: flex;
      flex-direction: column;
      gap: 1.2rem;
      width: 100%;
      max-width: 400px;
      padding: 0 1rem;
    }

    .nav-buttons a {
      background: rgba(255, 255, 255, 0.15);
      color: #fff;
      font-weight: 600;
      font-size: 1.25rem;
      padding: 1.1rem 2rem;
      border-radius: 14px;
      border: 1.5px solid rgba(255, 255, 255, 0.25);
      text-align: center;
      text-decoration: none;
      backdrop-filter: blur(12px);
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
      cursor: pointer;
      transition: 
        background-color 0.4s ease,
        color 0.4s ease,
        box-shadow 0.4s ease,
        transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.6rem;
      user-select: none;
    }

    .nav-buttons a:hover,
    .nav-buttons a:focus {
      background: #fff;
      color: #001f3f;
      box-shadow: 0 12px 24px rgba(0, 63, 255, 0.35);
      transform: translateY(-6px) scale(1.04);
      outline: none;
      z-index: 10;
    }

    .nav-buttons a:active {
      transform: translateY(-2px) scale(0.98);
      box-shadow: 0 6px 12px rgba(0, 63, 255, 0.3);
    }

    /* Emojis style */
    .nav-buttons a > svg,
    .nav-buttons a > .emoji {
      font-size: 1.5rem;
      line-height: 1;
      flex-shrink: 0;
      vertical-align: middle;
    }

    /* Smaller emoji for dashboard */
    .nav-buttons a.dashboard-emoji {
      font-size: 1.2rem !important;
      user-select: none;
    }

    @media (min-width: 600px) {
      h1 {
        font-size: 5rem;
      }

      .nav-buttons a {
        font-size: 1.4rem;
        padding: 1.25rem 2.5rem;
      }
    }
  </style>
</head>
<body>

  <h1>آموزشگاه زبان</h1>

  <nav class="nav-buttons" role="navigation" aria-label="لینک‌های ناوبری اصلی">
    <a href="./NewYear" aria-label="تبریک نوروز 🎉">
      <span class="emoji" aria-hidden="true">🎉</span> تبریک نوروز
    </a>
    <a href="./school" aria-label="نمونه سوالات مدارس 📚">
      <span class="emoji" aria-hidden="true">📚</span> نمونه سوالات مدارس
    </a>
    <a href="./dashboard.php" aria-label="داشبورد">
      <span class="emoji dashboard-emoji" aria-hidden="true">📊</span> داشبورد
    </a>
  </nav>

</body>
</html>
