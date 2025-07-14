
<!DOCTYPE html>
<html>
<head>
    <title> @yield('code','500') | {{ config('app.name','威四方') }}</title>

    <meta charset="utf-8">
    <link rel="icon" href="{{ asset('static/images/favicon.ico') }}" sizes="any">
    <meta name="keywords" content="{{ config('app.name','威四方') }}">
    <meta name="description" content="威四方是一个为企业和个体客户提供信息综合服务的平台；包含客户关系管理系统(CRM)、仓库管理系统(WMS)、采购系统(SRM)、在线文档(DOCS)、在线相册(PHOTOS)、企业智能办公系统(OA)、企业资源计划管理(ERP)、在线工具(tools)、个性化定制等服务项目;以客户成功为我们的宗旨。">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">

    <meta name="author" content="{{ config('app.name','威四方') }}" />
    <meta name="copyright" content="{{ config('app.name','威四方') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* 提示框样式 */
        .message-box{padding:20px;position:relative;text-align:center;font-size:24px;color:#196aa8;}@keyframes flash{0%{opacity:1;}50%{opacity:0;}100%{opacity:1;}}.rect{background:linear-gradient(to left,#196aa8,#196aa8) left top no-repeat,linear-gradient(to bottom,#196aa8,#196aa8) left top no-repeat,linear-gradient(to left,#196aa8,#196aa8) right top no-repeat,linear-gradient(to bottom,#196aa8,#196aa8) right top no-repeat,linear-gradient(to left,#196aa8,#196aa8) left bottom no-repeat,linear-gradient(to bottom,#196aa8,#196aa8) left bottom no-repeat,linear-gradient(to left,#196aa8,#196AA8) right bottom no-repeat,linear-gradient(to left,#196aa8,#196aa8) right bottom no-repeat;background-size:2px 15px,20px 2px,2px 15px,20px 2px;}#main:has(.error-tips){padding:4px!important;}
    </style>
    <style>
        :root{--primary-color:#409eff;--primary-light:#2a2a2a;--primary-dark:#66b1ff;--bg-color:#1a1a1a;--bg-secondary:#2a2a2a;--text-color:#e6e6e6;--text-light:#a0a0a0;--text-lighter:#7a7a7a;--border-color:#4c4c4c;--border-light:#3a3a3a;--sidebar-bg:#2a2a2a;--code-bg:#282c34;--code-text:#abb2bf;--code-border:#3e4451;--code-line-numbers:#5c6370;--bg-book-card:#1a1a1a;--body-bg-color:#1a1a1a;--card-box-shadow:1px 1px 4px 1px rgba(255,255,255,0.4);}
        body{margin:0;height:100vh;display:flex;flex-direction:column;justify-content:normal;align-items:center;background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);font-family:"Segoe UI",system-ui,sans-serif;}.tip-box .status-icon{width:125px;height:125px;border-radius:50%;color:white;font-size:100px;font-weight:bold;display:flex;justify-content:center;align-items:center;margin:60px auto 25px auto;position:relative;animation:bounce 0.8s ease infinite alternate;}.tip-box .error .status-icon{background:#ff4757;box-shadow:0 5px 15px rgba(255,71,87,0.3);}.tip-box .success .status-icon{background:#2ed573;box-shadow:0 5px 15px rgba(46,213,115,0.3);}.tip-box .warning .status-icon{background:#ffa502;box-shadow:0 5px 15px rgba(255,165,2,0.3);}.tip-box .info .status-icon{background:#1e90ff;box-shadow:0 5px 15px rgba(30,144,255,0.3);}.tip-box .status-icon::after{content:"";position:absolute;width:120%;height:120%;border-radius:50%;opacity:0;animation:ripple 2s ease-out infinite;}.tip-box .error .status-icon::after{border:2px solid #ff4757;}.tip-box .success .status-icon::after{border:2px solid #2ed573;}.tip-box .warning .status-icon::after{border:2px solid #ffa502;}.tip-box .info .status-icon::after{border:2px solid #1e90ff;}.tip-box .message{font-size:25px;font-weight:600;color:#2f3542;letter-spacing:0.5px;text-shadow:0 2px 4px rgba(0,0,0,0.05);position:relative;margin-bottom:30px;}.tip-box .message::after{content:"";position:absolute;bottom:-10px;left:50%;transform:translateX(-50%);width:50px;height:3px;border-radius:3px;}.tip-box .error .message::after{background:#ff4757;}.tip-box .success .message::after{background:#2ed573;}.tip-box .warning .message::after{background:#ffa502;}.tip-box .info .message::after{background:#1e90ff;}.tip-box .error .message{color:#ff4757;}.tip-box .success .message{color:#2ed573;}.tip-box .warning .message{color:#ffa502;}.tip-box .info .message{color:#1e90ff;}.tip-box .describe{font-size:16px;color:var(--text-color);letter-spacing:0.5px;text-shadow:0 2px 4px rgba(0,0,0,0.05);position:relative;margin-bottom:30px;}.tip-box .action-button{padding:6px 20px;margin-top:20px;border:none;border-radius:50px;font-size:16px;font-weight:600;cursor:pointer;transition:all 0.3s ease;box-shadow:0 4px 10px rgba(0,0,0,0.1);}.tip-box .error .action-button{background:#ff4757;color:white;}.tip-box .success .action-button{background:#2ed573;color:white;}.tip-box .warning .action-button{background:#ffa502;color:white;}.tip-box .info .action-button{background:#1e90ff;color:white;}.tip-box .error,.tip-box .success,.tip-box .warning,.tip-box .info{text-align:center;}.tip-box .action-button:hover{transform:translateY(-2px);box-shadow:0 6px 15px rgba(0,0,0,0.15);}@keyframes bounce{to{transform:translateY(-15px);}}@keyframes ripple{to{transform:scale(1.5);opacity:0;}}.footer{position:fixed;bottom:0;width:100vw;color:#d1d1d1;grid-area:footer;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2px 4px;background-color:#2a2a2a;border-top:1px solid #444;min-height:60px;text-align:center;}.footer-links{display:flex;gap:15px;margin-top:0;}@media (max-width:900px){.footer-links{flex-wrap:wrap;justify-content:center;gap:0 40px;}}.footer a{color:#d1d1d1;transition:all 0.3s ease;font-size:12px;}.font-12{font-size:12px;}.footer a:hover{color:#1ab394;}
    </style>
</head>

<body>
    <!-- 操作状态提示 -->
    <div class="tip-box">
        <!-- 提示:info、warning、error、success -->
        <div class="error">
            <div class="status-icon">!</div>
            <div class="message">@yield('code','500'):{{ __( !empty($message) ? $message : 'Page Not Found') }}</div>
            <div class="describe">{!! empty($describe)?'':$describe !!}</div>

            <button class="action-button" onclick="window.location.href='{{$btn["url"]??"/"}}'">{{$btn['text']??'返回首页'}}</button>

        </div>
    </div>

    <!-- 页脚 -->
    <footer class="footer">
        <div class="footer-links">
            <a href="#">关于我们</a>
            <a href="#">帮助中心</a>
            <a href="#">隐私政策</a>
        </div>
        <div class="footer-links">
            <div class="font-12">&copy<script>document.write(new Date().getFullYear());</script> <span id="footerText">威四方. 保留所有权利.</span></div>
            <a href="https://beian.mps.gov.cn/#/query/webSearch?code=53010202002026" class="d-none" rel="noreferrer" target="_blank">
                <img class="d-none" src="{{'/static/images/system/beian.png'}}" alt="BeiAn Logo" style="width: 12px;height: 12px;margin-bottom: -2px;">
                滇公网安备53010202002026
            </a>
            <a class="" href="https://beian.miit.gov.cn" target="_blank">滇ICP备16003347号-2</a>
        </div>
    </footer>
</body>

<script src="{{'/static/libs/zxf/js/my_console.min.js'}}"></script>
<script> my_console.version(); </script>
</html>
