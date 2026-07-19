<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نوروز مبارک</title>
    <link href="https://cdn.jsdelivr.net/npm/vazir-font@4.0.1/dist/font-face.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/iransans@4.0.0/dist/css/iran-sans.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/confetti-js"></script>
    <style>
        @font-face {
            font-family: 'BNazanin';
            src: url('fonts/BNazanin.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'BNazanin';
            src: url('fonts/BNaznnBd.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        body {
            background: url('images/nowruz-bg.jpg') no-repeat center center;
            background-size: cover; /* تصویر بک‌گراند به اندازه صفحه کشیده می‌شود */
            background-attachment: fixed; /* پس‌زمینه ثابت می‌ماند هنگام اسکرول */
            text-align: center;
            font-family: 'BNazanin', sans-serif;
            overflow: hidden;
            position: relative;
        }
        @media (max-width: 768px) {
            body {
                background: url('images/nowruz-mobile.jpg') no-repeat center center;
                /* background: url('images/nowruz.jpg') no-repeat center center; */
                background-size: cover;
                background-attachment: fixed;
            }
        }
        @media (max-width: 768px) {
            .glow-text {
                font-size: 1.5rem; /* کوچکتر کردن فونت */
                line-height: 1.2; /* کاهش فاصله بین خطوط */
            }
                .mobile-break {
                display: block;
            }
        }
        


        .container {
            opacity: 0;
            position: relative;
        }
        .card {
            background: rgba(255, 255, 255, 0);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            backdrop-filter: blur(12px);
        }
        .glow-text {
            color: #206A5D;
            text-shadow: 0 0 15px rgba(32, 106, 93, 0.6);
            font-size: 1.9rem;
            font-weight: bold;
        }
        #falling-blossoms {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: -1;
        }
        .logo {
            width: 150px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="flex items-center justify-center h-screen relative text-right" >
    <canvas id="falling-blossoms"></canvas>
    <div class="container max-w-lg mx-auto p-6" data-aos="fade-up">
        <div>
            <!-- لوگو -->
            <img src="images/logo.png" alt="لوگو شرکت" class="logo">
            <h1 class="glow-text text-center">
                همکار عزیز<span class="mobile-break"><br></span> نوروزتان پیروز
            </h1>

	
            </p>
            <p class="mt-4 text-gray-600 font-semibold text-center">
                نوروز، این پیام‌آور نو شدن و امید، فرصتی است تا قدردان زحمات بی‌وقفه‌تان باشیم<br>
سال نو را به شما تبریک می‌گوییم و برایتان سالی سرشار از انرژی، سلامتی و موفقیت‌های روزافزون آرزو می‌کنیم 
            </p>
<p class="text-center">
                brand name
            </p>

            <audio controls class="mt-4 w-full">
                <source src="nowruz.mp3" type="audio/mpeg">
                مرورگر شما از پخش موسیقی پشتیبانی نمی‌کند
            </audio>
            
        </div>
    </div>
    <script>
        gsap.to(".container", {opacity: 1, y: 0, duration: 1.2, ease: "power2.out"});
        AOS.init();
        
        const canvas = document.getElementById("falling-blossoms");
        const confettiSettings = { target: canvas, max: 100, size: 2, clock: 15, props: ['circle', 'square', 'triangle'], colors: [[255, 182, 193], [240, 128, 128], [255, 105, 180]] };
        const confetti = new ConfettiGenerator(confettiSettings);
        confetti.render();
    </script>
</body>
</html>