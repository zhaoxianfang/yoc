@extends('docs::layouts_v2.layout')
@section('title', '提示')

@section('top_nav_tabs')
    <a href="{{ url('/docs') }}">
        <li class="nav-tab">广场</li>
    </a>
    @if (auth()->check())
        <a href="{{ url('/docs/my') }}">
            <li class="nav-tab">我的</li>
        </a>
        <a href="{{ url('/docs/create') }}">
            <li class="nav-tab">新建</li>
        </a>
    @endif
@endsection

@section('head_css')

    <style>
        /* 基础图标样式 */
        .tip-box .status-icon {
            width: 125px;
            height: 125px;
            border-radius: 50%;
            color: white;
            font-size: 100px;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 60px auto 25px auto;
            position: relative;
            animation: bounce 0.8s ease infinite alternate;
        }

        /* 不同类型图标样式 */
        .tip-box .error .status-icon {
            background: #ff4757;
            box-shadow: 0 5px 15px rgba(255, 71, 87, 0.3);
        }
        .tip-box .success .status-icon {
            background: #2ed573;
            box-shadow: 0 5px 15px rgba(46, 213, 115, 0.3);
        }
        .tip-box .warning .status-icon {
            background: #ffa502;
            box-shadow: 0 5px 15px rgba(255, 165, 2, 0.3);
        }
        .tip-box .info .status-icon {
            background: #1e90ff;
            box-shadow: 0 5px 15px rgba(30, 144, 255, 0.3);
        }

        /* 波纹效果 */
        .tip-box .status-icon::after {
            content: "";
            position: absolute;
            width: 120%;
            height: 120%;
            border-radius: 50%;
            opacity: 0;
            animation: ripple 2s ease-out infinite;
        }

        .tip-box .error .status-icon::after {
            border: 2px solid #ff4757;
        }
        .tip-box .success .status-icon::after {
            border: 2px solid #2ed573;
        }
        .tip-box .warning .status-icon::after {
            border: 2px solid #ffa502;
        }
        .tip-box .info .status-icon::after {
            border: 2px solid #1e90ff;
        }

        /* 消息文本样式 */
        .tip-box .message {
            font-size: 25px;
            font-weight: 600;
            color: #2f3542;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: relative;
            margin-bottom: 30px;
        }

        /* 不同类型消息下划线 */
        .tip-box .message::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            border-radius: 3px;
        }

        .tip-box .error .message::after {
            background: #ff4757;
        }
        .tip-box .success .message::after {
            background: #2ed573;
        }
        .tip-box .warning .message::after {
            background: #ffa502;
        }
        .tip-box .info .message::after {
            background: #1e90ff;
        }

        .tip-box .error .message {
            color: #ff4757;
        }
        .tip-box .success .message {
            color: #2ed573;
        }
        .tip-box .warning .message {
            color: #ffa502;
        }
        .tip-box .info .message {
            color: #1e90ff;
        }

        .tip-box .describe{
            font-size: 16px;
            color: var(--text-color);
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: relative;
            margin-bottom: 30px;
        }

        /* 按钮样式 */
        .tip-box .action-button {
            padding: 6px 20px;
            margin-top: 20px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .tip-box .error .action-button {
            background: #ff4757;
            color: white;
        }
        .tip-box .success .action-button {
            background: #2ed573;
            color: white;
        }
        .tip-box .warning .action-button {
            background: #ffa502;
            color: white;
        }
        .tip-box .info .action-button {
            background: #1e90ff;
            color: white;
        }
        .tip-box .error ,.tip-box .success,.tip-box .warning,.tip-box .info {text-align: center;}

        .tip-box .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }

        /* 动画效果 */
        @keyframes bounce {
            to { transform: translateY(-15px); }
        }
        @keyframes ripple {
            to {
                transform: scale(1.5);
                opacity: 0;
            }
        }
    </style>
@endsection

@section('content')
    <!-- 操作状态提示 -->
    <div class="tip-box">
        <!-- 提示:info、warning、error、success -->
        <div class="@yield('tip_type','info')">
            <div class="status-icon">@yield('tip_icon','i')</div>
            <div class="message">{{ __( !empty($message) ? $message : '出错啦！') }}</div>
            <div class="describe">{!! empty($describe)?'':$describe !!}</div>
            @if(!empty($btn) && !empty($btn['url']))
            <button class="action-button" onclick="window.location.href='{{$btn["url"]}}'">{{$btn['text']??'查看'}}</button>
            @endif
        </div>
    </div>
@endsection

@section('page_js')
@endsection
