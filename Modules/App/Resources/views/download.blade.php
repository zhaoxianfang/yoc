<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APP下载|{{config('app.name')}}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: #000;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* 响应式背景动画 */
        canvas {
            position: absolute;
            top: 0;
            left: 0;
            z-index: -1;
            width: 100%;
            height: 100%;
        }

        /* 页面主要容器 */
        .container {
            text-align: center;
            z-index: 1;
            position: relative;
        }

        .logo {
            width: 150px;
            height: 150px;
            background: url('{{ asset('/static/images/logo/logo.jpg') }}') no-repeat center/contain;
            border-radius: 50%;
            margin: 0 auto;
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.7);
            animation: pulseGlow 3s infinite;
        }

        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(0, 255, 255, 0.7);
            }
            50% {
                box-shadow: 0 0 40px rgba(0, 255, 255, 0.9);
            }
        }

        h1 {
            font-size: 3rem;
            margin: 20px 0;
            text-transform: uppercase;
            color: #00ffff;
            background: linear-gradient(90deg, #00ffff, #ff00ff);
            -webkit-background-clip: text;
            background-clip: text;
            animation: textAnimation 4s linear infinite;
        }

        @keyframes textAnimation {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }

        p {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.8);
        }

        /* 按钮 */
        .btn-download {
            padding: 12px 30px;
            background: rgba(0, 255, 255, 0.2);
            border: 2px solid rgba(0, 255, 255, 0.7);
            border-radius: 30px;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            position: relative;
            transition: background 0.3s, box-shadow 0.3s, transform 0.3s;
        }

        .btn-download:hover {
            background: rgba(0, 255, 255, 0.5);
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.7);
            transform: scale(1.1);
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }

            .btn-download {
                padding: 10px 20px;
                font-size: 1rem;
            }

            .logo {
                width: 120px;
                height: 120px;
            }
        }

        /* 响应式设计适应不同屏幕 */
        @media (max-width: 480px) {
            h1 {
                font-size: 1.5rem;
            }

            p {
                font-size: 1rem;
            }

            .logo {
                width: 100px;
                height: 100px;
            }

            .btn-download {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }

    </style>
</head>
<body>

<!-- 星空背景 -->
<canvas id="starfield"></canvas>

<!-- 页面内容 -->
<div class="container">
    <div class="logo"></div>
    <h1>{{ config('app.name') }} APP下载</h1>
    <p>当前版本：V{{ $androidInfo->version??'暂无' }}</p>
    <p>平台类型：{{ $androidInfo->platform??'暂无' }}</p>
    <p>威四方，致力于打造您身边的智能助手!</p>
    <p>体验最新的科技应用，让你的生活更加便捷!</p>
    <button class="btn-download" id="download">立即下载</button>
</div>

<script>
    // 背景星空动画
    const canvas = document.getElementById("starfield");
    const ctx = canvas.getContext("2d");
    const stars = [];
    const numStars = 200;

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    function init() {
        for (let i = 0; i < numStars; i ++) {
            stars.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                radius: Math.random() * 2 + 0.5,
                speed: Math.random() * 0.5 + 0.1
            });
        }
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        for (let i = 0; i < stars.length; i ++) {
            const star = stars[i];
            ctx.beginPath();
            ctx.arc(star.x, star.y, star.radius, 0, Math.PI * 2);
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.fill();
        }
    }

    function update() {
        for (let i = 0; i < stars.length; i ++) {
            stars[i].y -= stars[i].speed;
            if (stars[i].y < 0) {
                stars[i].y = canvas.height;
            }
        }
    }

    function animate() {
        draw();
        update();
        requestAnimationFrame(animate);
    }

    init();
    animate();
</script>

</body>
<script>
    const downloadButton = document.getElementById("download");
    // 点击下载按钮时候请求服务器url发送app下载请求，并且避免下载按钮被重复点击
    downloadButton.addEventListener("click", function () {
        downloadButton.disabled = true;
        window.location.href = '{{ url('app/download/android') }}';
    });
</script>
</html>
