<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')|{{ config('app.name','威四方') }}</title>
    <meta name="keywords" content="威四方,云指南,云南,服务,定制,科技,智能助手,项目开发,weisifang,01.yn.cn,智慧服务">
    <meta name="author" content="威四方,云指南" />
    <meta name="copyright" content="威四方,云指南" />
    <meta name="description" content="威四方/云指南 威四方/云指南 致力于打造您身边的智能助手，探索未来科技，开启无限可能。">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}body{font-family:'Arial',sans-serif;background:linear-gradient(135deg,#1e1e1e,#2a2a2a);color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;text-align:center;overflow:hidden;position:relative;background-size:400% 400%;animation:gradientMove 20s ease infinite;width:100%;box-sizing:border-box;}@keyframes gradientMove{0%{background-position:0% 50%;}50%{background-position:100% 50%;}100%{background-position:0% 50%;}}h1{font-size:2.5rem;margin-bottom:20px;letter-spacing:2px;text-shadow:0 0 20px rgba(0,255,255,0.8),0 0 30px rgba(0,255,255,0.6),0 0 40px rgba(0,255,255,0.4);animation:glow 1.5s infinite ease-in-out,moveText 5s infinite alternate;white-space:nowrap;display:inline-block;opacity:0;animation:fadeInGlow 2s forwards 0.5s;}@keyframes fadeInGlow{0%{opacity:0;transform:translateY(20px);}100%{opacity:1;transform:translateY(0);}}@keyframes glow{0%{text-shadow:0 0 5px rgba(0,255,255,0.6),0 0 10px rgba(0,255,255,0.4);}50%{text-shadow:0 0 20px rgba(0,255,255,1),0 0 30px rgba(0,255,255,0.8);}100%{text-shadow:0 0 5px rgba(0,255,255,0.6),0 0 10px rgba(0,255,255,0.4);}}@keyframes moveText{0%{transform:translateY(0px);}100%{transform:translateY(-10px);}}.clock{font-size:2rem;margin-top:20px;color:#00ffff;letter-spacing:1px;animation:blink 1s infinite alternate;opacity:0;animation:fadeInClock 2s forwards 1.5s;}@keyframes blink{0%{opacity:0.8;}100%{opacity:1;}}@keyframes fadeInClock{0%{opacity:0;transform:translateY(20px);}100%{opacity:1;transform:translateY(0);}}.content{margin-top:1px;font-size:1.5rem;color:#b0b0b0;max-width:80%;line-height:1.5;opacity:0;animation:fadeInContent 3s forwards 1s;animation-timing-function:ease-out;word-wrap:break-word;word-break:break-word;}@keyframes fadeInContent{0%{opacity:0;transform:translateY(20px);}100%{opacity:1;transform:translateY(0);}}@media (max-width:768px){h1{font-size:2.0rem;}.clock{font-size:1.5rem;}.content{font-size:1.2rem;max-width:90%;}}@media (max-width:480px){h1{font-size:2.5rem;}.clock{font-size:1rem;}.content{font-size:1rem;max-width:90%;}}.particle-container{position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:-1;overflow:hidden;}.particle{position:absolute;border-radius:50%;background:rgba(0,255,255,0.7);opacity:0;animation:moveParticle 5s infinite ease-in-out;}@keyframes moveParticle{0%{transform:translate3d(0,0,0);opacity:1;}100%{transform:translate3d(calc(100vw * 1.5),calc(100vh * 1.5),0);opacity:0;}}.glow-text{font-size:1.5rem;color:#00ffff;text-shadow:0 0 10px rgba(0,255,255,0.8),0 0 20px rgba(0,255,255,0.6),0 0 30px rgba(0,255,255,0.4);animation:glowPulse 2s infinite ease-in-out;margin-top:20px;}@keyframes glowPulse{0%{text-shadow:0 0 10px rgba(0,255,255,0.8),0 0 20px rgba(0,255,255,0.6),0 0 30px rgba(0,255,255,0.4);}50%{text-shadow:0 0 20px rgba(0,255,255,1),0 0 30px rgba(0,255,255,0.9),0 0 40px rgba(0,255,255,0.8);}100%{text-shadow:0 0 10px rgba(0,255,255,0.8),0 0 20px rgba(0,255,255,0.6),0 0 30px rgba(0,255,255,0.4);}}.grid-overlay{position:absolute;top:0;left:0;width:100%;height:100%;background-image:linear-gradient(0deg,rgba(0,255,255,0.1) 1px,transparent 1px),linear-gradient(90deg,rgba(0,255,255,0.1) 1px,transparent 1px);background-size:30px 30px;pointer-events:none;z-index:-2;animation:gridMove 10s linear infinite;}@keyframes gridMove{0%{background-position:0 0;}100%{background-position:-100% -100%;}}.btn{margin-top:30px;padding:15px 30px;font-size:1.2rem;background:linear-gradient(135deg,#00ffff,#00bfff);border:none;color:white;border-radius:25px;cursor:pointer;outline:none;box-shadow:0 4px 10px rgba(0,255,255,0.5);transition:background 0.3s ease,transform 0.3s ease;}.btn:hover{background:linear-gradient(135deg,#00bfff,#00ffff);transform:scale(1.1);}.font-12{font-size: 12px!important;}
    </style>
</head>
<body>

<h1>@yield('code')</h1>
<div class="content">@yield('message')</div>
<div class="content font-12">@yield('tips')</div>
<div class="clock" id="clock"></div>
<button class="btn" id="to_more">返回首页</button>

<div class="particle-container" id="particle-container"></div>
<div class="grid-overlay"></div>

<script>
    // 实时更新时间
    function updateClock() {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth() + 1;  // 月份从0开始
        const day = now.getDate();
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const seconds = now.getSeconds();
        const timeString = `${year}/${month < 10 ? '0' + month : month}/${day < 10 ? '0' + day : day} ${hours < 10 ? '0' + hours : hours}:${minutes < 10 ? '0' + minutes : minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
        document.getElementById('clock').textContent = timeString;
    }

    setInterval(updateClock, 1000);  // 每秒更新时间

    // 粒子效果
    const particleContainer = document.getElementById('particle-container');

    function createParticles() {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        const size = Math.random() * 5 + 'px';
        particle.style.width = size;
        particle.style.height = size;
        particle.style.top = Math.random() * 100 + 'vh';
        particle.style.left = Math.random() * 100 + 'vw';
        particleContainer.appendChild(particle);
        setTimeout(() => particle.remove(), 5000); // 粒子生命周期5秒
    }

    setInterval(createParticles, 100);

    // 了解更多
    document.getElementById('to_more').addEventListener('click', function() {
        window.location.href = '/';
    });
</script>
</body>
</html>
