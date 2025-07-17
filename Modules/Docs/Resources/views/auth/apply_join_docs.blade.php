
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>加入文档申请|{{ config('app.name','威四方') }}</title>
    <style type="text/css" media="screen">
        body{margin:0;background-color:#fbfbfb;text-align:center}.docs_form{--color-primary:#44a0b3;--color-grey:rgba(68,160,179,.06);--color-dark:rgba(68,160,179,.5);--color-semidark:rgba(68,160,179,.5);text-align:center;margin:24px 0 0 0;font-family:'Segoe UI';font-size:14px}.docs_form .docs_form-wrapper{-webkit-transition:all 1s;-o-transition:all 1s;transition:all 1s;-webkit-perspective:1000px;perspective:1000px;position:relative;height:100%;width:360px;margin:0 auto}.docs_form.docs_form-red{--color-primary:#ff6464;--color-grey:rgba(255,100,100,.06);--color-dark:rgba(255,100,100,.8);--color-semidark:rgba(255,100,100,.55)}.docs_form.docs_form-green{--color-primary:#d0ef84;--color-grey:rgba(208,239,132,.15);--color-dark:rgba(208,239,132,1);--color-semidark:rgba(208,239,132,.6)}.docs_form.docs_form-purple{--color-primary:#6c567b;--color-grey:rgba(108,86,123,.08);--color-dark:rgba(108,86,123,.7);--color-semidark:rgba(108,86,123,.45)}.docs_form.docs_form-blue{--color-primary:#0081c6;--color-grey:rgba(0,129,198,.05);--color-dark:rgba(0,129,198,.7);--color-semidark:rgba(0,129,198,.45)}.docs_form a{color:var(--color-primary);text-decoration:none;border-bottom:1px dashed var(--color-semidark);margin-top:-3px;padding-bottom:2px}.docs_form *{-webkit-box-sizing:border-box;box-sizing:border-box}.docs_form .logo-brand{overflow:hidden;width:100px;height:100px;margin:0 auto -50px auto;border-radius:50%;-webkit-box-shadow:0 4px 40px rgba(0,0,0,.07);box-shadow:0 4px 40px rgba(0,0,0,.07);padding:10px;background-color:#fff;z-index:1;position:relative}.docs_form .logo-brand img{width:100%}.docs_form .docs_form-box{width:100%;position:absolute;left:0}.docs_form .docs_form-box-inner{background-color:#fff;-webkit-box-shadow:0 7px 25px rgba(0,0,0,.08);box-shadow:0 7px 25px rgba(0,0,0,.08);padding:60px 25px 25px 25px;text-align:left;border-radius:3px}.docs_form .docs_form-box::after{content:' ';-webkit-box-shadow:0 0 25px rgba(0,0,0,.1);box-shadow:0 0 25px rgba(0,0,0,.1);-webkit-transform:translate(0,-92.6%) scale(.88);-ms-transform:translate(0,-92.6%) scale(.88);transform:translate(0,-92.6%) scale(.88);border-radius:3px;position:absolute;top:100%;left:0;width:100%;height:100%;background-color:#fff;z-index:-1}.docs_form .docs_form-box.docs_form-flip{-webkit-transform:rotate3d(0,1,0,-180deg);transform:rotate3d(0,1,0,-180deg);display:none;opacity:0}.docs_form .docs_form-box p{color:var(--color-semidark);font-weight:700;margin-bottom:20px;text-align:center}.docs_form .docs_form-box .docs_form-group{margin-bottom:10px}.docs_form .docs_form-box label{margin-bottom:5px;display:inline-block;width:100%;color:var(--color-semidark);font-weight:700}.docs_form .docs_form-box label a{float:right}.docs_form .docs_form-box .docs_form-input{background-color:var(--color-grey);color:var(--color-dark);border:0;border-radius:3px;padding:15px 20px;width:100%;outline:0}.docs_form .docs_form-box .docs_form-btn{display:inline-block;width:100%;border:0;color:#fff;padding:15px;border-radius:3px;background-color:var(--color-primary);-webkit-box-shadow:0 2px 7px var(--color-semidark);box-shadow:0 2px 7px var(--color-semidark);font-weight:700;outline:0;cursor:pointer;-webkit-transition:all .5s;-o-transition:all .5s;transition:all .5s}.docs_form .docs_form-box .docs_form-btn:active{-webkit-box-shadow:none;box-shadow:none}.docs_form .docs_form-box .docs_form-btn:hover{opacity:.9}.docs_form .text-foot{text-align:center;padding:10px;font-weight:700;margin-top:10px;color:var(--color-semidark)}.docs_form .docs_form-footer{text-align:center;margin:30px 0;font-size:12px;color:var(--color-semidark);font-weight:700}.docs_form .docs_form-box.docs_form-animated{-webkit-animation-name:docs_formAnimated;animation-name:docs_formAnimated;-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-fill-mode:forwards;animation-fill-mode:forwards;-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}.docs_form .docs_form-box.docs_form-animatedback{-webkit-animation-name:docs_formAnimatedBack;animation-name:docs_formAnimatedBack;-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-fill-mode:forwards;animation-fill-mode:forwards;-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}.docs_form .docs_form-box.docs_form-animated-flip{-webkit-animation-name:docs_formAnimatedFlip;animation-name:docs_formAnimatedFlip;-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-fill-mode:forwards;animation-fill-mode:forwards;-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}.docs_form .docs_form-box.docs_form-animated-flipback{-webkit-animation-name:docs_formAnimatedFlipBack;animation-name:docs_formAnimatedFlipBack;-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-fill-mode:forwards;animation-fill-mode:forwards;-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}
        .docs_form .logo-brand.docs_form-animated{-webkit-animation-name:docs_formBrandAnimated;animation-name:docs_formBrandAnimated;-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-fill-mode:forwards;animation-fill-mode:forwards;-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}.login-item-row{padding:20px;display:flex;flex-direction:row;justify-content:space-between}.login-item-row .item{flex:1;text-align:center}.login-item-row .item img{width:50px;height:50px;text-align:center}@-webkit-keyframes docs_formAnimated{0%{-webkit-transform:rotate3d(0);transform:rotate3d(0)}99%{opacity:1}100%{opacity:0;-webkit-transform:rotate3d(0,1,0,180deg);transform:rotate3d(0,1,0,180deg)}}@keyframes docs_formAnimated{0%{-webkit-transform:rotate3d(0);transform:rotate3d(0)}99%{opacity:1}100%{opacity:0;-webkit-transform:rotate3d(0,1,0,180deg);transform:rotate3d(0,1,0,180deg)}}@-webkit-keyframes docs_formAnimatedBack{0%{opacity:0;-webkit-transform:rotate3d(0,1,0,180deg);transform:rotate3d(0,1,0,180deg)}99%{opacity:1}100%{opacity:1;-webkit-transform:rotate3d(0);transform:rotate3d(0)}}@keyframes docs_formAnimatedBack{0%{opacity:0;-webkit-transform:rotate3d(0,1,0,180deg);transform:rotate3d(0,1,0,180deg)}99%{opacity:1}100%{opacity:1;-webkit-transform:rotate3d(0);transform:rotate3d(0)}}@-webkit-keyframes docs_formAnimatedFlip{0%{-webkit-transform:rotate3d(0,1,0,-180deg);transform:rotate3d(0,1,0,-180deg);opacity:0}99%{opacity:1}100%{opacity:1;-webkit-transform:rotate3d(0,0,0,180deg);transform:rotate3d(0,0,0,180deg)}}@keyframes docs_formAnimatedFlip{0%{-webkit-transform:rotate3d(0,1,0,-180deg);transform:rotate3d(0,1,0,-180deg);opacity:0}99%{opacity:1}100%{opacity:1;-webkit-transform:rotate3d(0,0,0,180deg);transform:rotate3d(0,0,0,180deg)}}@-webkit-keyframes docs_formAnimatedFlipBack{0%{opacity:1;-webkit-transform:rotate3d(0,0,0,180deg);transform:rotate3d(0,0,0,180deg)}95%{opacity:0}100%{-webkit-transform:rotate3d(0,1,0,-180deg);transform:rotate3d(0,1,0,-180deg);opacity:0}}@keyframes docs_formAnimatedFlipBack{0%{opacity:1;-webkit-transform:rotate3d(0,0,0,180deg);transform:rotate3d(0,0,0,180deg)}95%{opacity:0}100%{-webkit-transform:rotate3d(0,1,0,-180deg);transform:rotate3d(0,1,0,-180deg);opacity:0}}@-webkit-keyframes docs_formBrandAnimated{0%{-webkit-transform:translate(0,0);transform:translate(0,0)}50%{-webkit-transform:translate(0,-120px);transform:translate(0,-120px)}100%{-webkit-transform:translate(0,0);transform:translate(0,0)}}@keyframes docs_formBrandAnimated{0%{-webkit-transform:translate(0,0);transform:translate(0,0)}50%{-webkit-transform:translate(0,-120px);transform:translate(0,-120px)}100%{-webkit-transform:translate(0,0);transform:translate(0,0)}}@-webkit-keyframes docs_formPasswordAnimated{0%{-webkit-transform:rotate3d(0,0,0,0);transform:rotate3d(0,0,0,0)}30%{opacity:1}60%{opacity:0}100%{opacity:0;-webkit-transform:rotate3d(1,0,0,-180deg);transform:rotate3d(1,0,0,-180deg);z-index:-1}}@keyframes docs_formPasswordAnimated{0%{-webkit-transform:rotate3d(0,0,0,0);transform:rotate3d(0,0,0,0)}30%{opacity:1}60%{opacity:0}100%{opacity:0;-webkit-transform:rotate3d(1,0,0,-180deg);transform:rotate3d(1,0,0,-180deg);z-index:-1}}@-webkit-keyframes docs_formPasswordAnimatedBack{0%{opacity:0;-webkit-transform:rotate3d(1,0,0,-180deg);transform:rotate3d(1,0,0,-180deg)}40%{opacity:0}60%{opacity:1}100%{-webkit-transform:rotate3d(0,0,0,0);transform:rotate3d(0,0,0,0)}}@keyframes docs_formPasswordAnimatedBack{0%{opacity:0;-webkit-transform:rotate3d(1,0,0,-180deg);transform:rotate3d(1,0,0,-180deg)}40%{opacity:0}60%{opacity:1}100%{-webkit-transform:rotate3d(0,0,0,0);transform:rotate3d(0,0,0,0)}}@media screen and (max-width:320px){.docs_form .docs_form-wrapper{width:100%}.docs_form .docs_form-box{padding:0 10px}}


        .login-img.selected {
            border: 5px solid green;
            position: relative;
        }
    </style>
</head>

<body>
<!-- https://www.sucaihuo.com/templates/5937.html -->
{{--在docs_form 加样式 .docs_form-red  .docs_form-green .docs_form-purple .docs_form-blue 控制主题，默认为空--}}
<div class="docs_form">
    <div class="logo-brand">
        <img src="{{ asset('static/images/logo.png') }}" alt="logo">
    </div>
    <div class="docs_form-wrapper">

        <div class="docs_form-box docs_form-login">
            <div class="docs_form-box-inner">
                <form method="post" action="{{ $url.'login' }}" class="unbind-form">
                    @csrf
                    @if (!empty($message))
                        <p>{{$message}}</p>
                    @else
                        <p>请选择一个登录方式</p>
                        <div class="docs_form-group login-item-row">
                            <div class="item">
                                <img class="login-img selected" data-type="qq" src="{{ asset('static/images/login/qq.png') }}" alt="qq 登录" style="border-radius: 50%; -webkit-box-shadow: 0 4px 40px rgba(0, 0, 0, .07);  box-shadow: 0 4px 40px rgba(0, 0, 0, .2); padding: 10px; background-color: #fff;">
                                <br />
                                <span>QQ登录</span>
                            </div>
                            <div class="item">
                                <img class="login-img" data-type="sina" src="{{ asset('static/images/login/sina.png') }}" alt="微博登录" style="border-radius: 50%; -webkit-box-shadow: 0 4px 40px rgba(0, 0, 0, .07);  box-shadow: 0 4px 40px rgba(0, 0, 0, .2); padding: 10px; background-color: #fff;">
                                <br />
                                <span>微博登录</span>
                            </div>
                        </div>
                        <input type="hidden" name="login_type" value="qq" id="select_login_type" />

                        <div class="docs_form-group">
                            <label>申请人</label>
                            <input type="text" name="extra_nickname" class="docs_form-input" value="{{ $data['extra_nickname']??'' }}" placeholder="例如：张三" required/>
                        </div>
                        <button class="docs_form-btn login-btn">
                            登录
                        </button>

                        <div class="text-foot">
                            没有帐户? <a href="javascript:;" class="register-link">注册</a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <div class="docs_form-box docs_form-register">
            <div class="docs_form-box-inner">
                <form method="post" action="{{ $url.'register' }}">
                    @csrf
                    @if (!empty($message))
                        <p>{{$message}}</p>
                    @else
                        <p>创建一个帐号</p>
                    @endif
                    <div class="docs_form-group">
                        <label>姓名</label>
                        <input type="text" name="nickname" class="docs_form-input" value="{{ $data['nickname']??'' }}" placeholder="请填写姓名" required/>
                    </div>
                    <div class="docs_form-group">
                        <label>邮箱</label>
                        <input type="email" name="email" class="docs_form-input" value="{{ $data['email']??'' }}" placeholder="请填写邮箱" required/>
                    </div>
                    <div class="docs_form-group">
                        <label>手机号</label>
                        <input type="number" name="mobile" class="docs_form-input" value="{{ $data['mobile']??'' }}" placeholder="请填写手机号" required/>
                    </div>
                    <div class="docs_form-group">
                        <label>登录密码</label>
                        <input type="text" name="password" class="docs_form-input" value="{{ $data['password']??'' }}" placeholder="请填写登录密码" required/>
                    </div>
                    <button class="docs_form-btn">
                        注册
                    </button>

                    <div class="text-foot">
                        已经有账户了? <a href="" class="login-link">登录</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="docs_form-box docs_form-set-remark">
            <div class="docs_form-box-inner">
                <form method="post" action="{{ $url.'remark' }}">
                    @csrf
                    <p>请填写申请人信息</p>
                    <div class="docs_form-group">
                        <label>申请人</label>
                        <input type="text" name="extra_nickname" class="docs_form-input" value="{{ $data['extra_nickname']??'' }}" placeholder="例如：张三" required/>
                    </div>
                    <button class="docs_form-btn">
                        提交
                    </button>

                    <div class="text-foot">
                    </div>
                </form>
            </div>
        </div>


    </div>
    <footer class="docs_form-footer">

    </footer>
</div>

<script type="text/javascript">
    var Auth={vars:{docs_form:document.querySelector('.docs_form'),logo_brand:document.querySelector('.logo-brand'),docs_form_wrapper:document.querySelector('.docs_form-wrapper'),docs_form_login:document.querySelector('.docs_form-login'),login_link:document.querySelector('.login-link'),login_btn:document.querySelector('.login-btn'),register_link:document.querySelector('.register-link'),docs_form_register:document.querySelector('.docs_form-register'),docs_form_footer:document.querySelector('.docs_form-footer'),box:document.getElementsByClassName('docs_form-box'),option:{}},register(e){Auth.vars.docs_form_login.className+=' docs_form-animated';setTimeout(()=>{Auth.vars.docs_form_login.style.display='none'},500);Auth.vars.docs_form_register.style.display='block';Auth.vars.docs_form_register.className+=' docs_form-animated-flip';Auth.setHeight(Auth.vars.docs_form_register.offsetHeight+Auth.vars.docs_form_footer.offsetHeight);e.preventDefault()},login(e){Auth.vars.docs_form_register.classList.remove('docs_form-animated-flip');Auth.vars.docs_form_register.className+=' docs_form-animated-flipback';Auth.vars.docs_form_login.style.display='block';Auth.vars.docs_form_login.classList.remove('docs_form-animated');Auth.vars.docs_form_login.className+=' docs_form-animatedback';setTimeout(()=>{Auth.vars.docs_form_register.style.display='none'},500);setTimeout(()=>{Auth.vars.docs_form_register.classList.remove('docs_form-animated-flipback');Auth.vars.docs_form_login.classList.remove('docs_form-animatedback')},1000);Auth.setHeight(Auth.vars.docs_form_login.offsetHeight+Auth.vars.docs_form_footer.offsetHeight);e.preventDefault()},setHeight(height){Auth.vars.docs_form_wrapper.style.minHeight=height+'px'},brand(){Auth.vars.logo_brand.classList+=' docs_form-animated';setTimeout(()=>{Auth.vars.logo_brand.classList.remove('docs_form-animated')},1000)},init(activeIndex=0){Auth.setHeight(Auth.vars.box[0].offsetHeight+Auth.vars.docs_form_footer.offsetHeight);var len=Auth.vars.box.length-1;for(var i=0;i<=len;i++){if(i!==parseInt(activeIndex)){Auth.vars.box[i].className+=' docs_form-flip'}}Auth.vars.register_link.addEventListener("click",(e)=>{Auth.brand();Auth.register(e)});Auth.vars.login_link.addEventListener("click",(e)=>{Auth.brand();Auth.login(e)})}}
    document.addEventListener('submit',async function(e){const form=e.target.closest('form');if(!form)return;e.preventDefault();e.stopImmediatePropagation();const submitter=e.submitter||form.querySelector('[type="submit"]');let submitterText=submitter?submitter.innerHTML:'';if(submitter){submitter.disabled=true;submitter.innerHTML=submitterText+'...';submitter.style.opacity='0.7';submitter.style.cursor='not-allowed'}let resetSubmit=function(){if(submitter){submitter.disabled=false;submitter.innerHTML=submitterText;submitter.style.opacity='1';submitter.style.cursor=''}};try{if(typeof form_intercept==='function'){if(form_intercept(e)===false){resetSubmit();return false}}if(typeof form_before==='function'){if(form_before()===false){resetSubmit();return false}}form.removeEventListener('submit',arguments.callee,true);form.submit()}catch(error){resetSubmit()}},true);
</script>
<script>
    // 获取所有具有指定 class 的元素
    var elements = document.getElementsByClassName('login-img');
    // 为每个元素添加点击事件监听器
    for (var i = 0; i < elements.length; i++) {
        elements[i].addEventListener('click', function() {
            // 在这里处理点击事件
            for (var j = 0; j < elements.length; j++) {
                // 删除其他元素的 test class
                if (elements[j] !== this) {
                    elements[j].classList.remove('selected');
                }
            }

            // 为被点击的元素添加 test class
            this.classList.add('selected');

            // 赋值
            document.getElementById("select_login_type").value = this.getAttribute('data-type');
        });
    }

    var page_index = parseInt("{{ $show_page_type }}") || 0;
    // 激活第几个页面 0:让用户选择QQ、微博等登录页面 或 仅提示提示信息； 1:用户重新注册新账号页面；2:用户填写昵称/备注页面
    Auth.init(page_index);
</script>
</body>
</html>
