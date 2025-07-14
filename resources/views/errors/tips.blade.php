<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> {{ __( !empty($title) ? $title : 'tips') }} | {{ config('app.name','威四方') }}</title>
    <style>
        body,html{margin:0;padding:0;height:100%;width:100%;display:flex;justify-content:center;align-items:center;font-family:Arial,sans-serif;background-color:#f0f0f0;overflow:hidden;}.message-box{padding:20px;position:relative;text-align:center;font-size:24px;color:#196aa8;}@keyframes flash{0%{opacity:1;}50%{opacity:0;}100%{opacity:1;}}.rect{background:linear-gradient(to left,#196aa8,#196aa8) left top no-repeat,linear-gradient(to bottom,#196aa8,#196aa8) left top no-repeat,linear-gradient(to left,#196aa8,#196aa8) right top no-repeat,linear-gradient(to bottom,#196aa8,#196aa8) right top no-repeat,linear-gradient(to left,#196aa8,#196aa8) left bottom no-repeat,linear-gradient(to bottom,#196aa8,#196aa8) left bottom no-repeat,linear-gradient(to left,#196aa8,#196AA8) right bottom no-repeat,linear-gradient(to left,#196aa8,#196aa8) right bottom no-repeat;background-size:2px 15px,20px 2px,2px 15px,20px 2px;}
    </style>
</head>
<body>
    <div class="message-box rect">
        @if(!empty($img))
            <div style="text-align: center;">
                <img src="{{$img}}" alt="Img" style="width: 50px;height: 50px;">
            </div>
        @endif
        {{ __( !empty($message) ? $message : 'tips') }}
    </div>
</body>
</html>
