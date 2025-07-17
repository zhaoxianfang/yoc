<style>
    .toc{display: none!important;}
    .app-container{grid-template-columns: 220px 1fr 10px!important;}
    .book-sum{height: unset!important;}
</style>
<link href="{{ asset('static/libs/zxf/css/bootstrap-form.css') }}" rel="stylesheet" type="text/css">

<div class="book-cards row no-gutters bg-white">
    <ul class="custom-tabs">
        <li><a href="#" name="#users">文档成员</a></li>
        <li><a href="#" name="#apply">待审用户</a></li>
        <li><a href="#" name="#invite">邀请用户</a></li>
    </ul>
    <div class="custom-tabs-content">
        <div id="users" class="tab-content">
            <div class="row">
                @include('docs::guide/app_user_list',['page_users' => $users])
            </div>
        </div>
        <div id="apply" class="tab-content">
            <div class="row">
                @include('docs::guide/app_user_list',['page_users' => $apply_users])
            </div>
        </div>
        <div id="invite" class="tab-content">
            <div class="row width-full">
                <img src="{{ $apply_img }}" alt="邀请用户扫码申请加入" style="margin: 0 auto;width: 170px;height: 190px;" data-tips title="邀请用户扫码申请加入本文档">
            </div>
        </div>
    </div>
</div>

<script>
console.log('users');
// 在每个 guide 页面都都定义一个 init_page 函数，来初始化页面事件
function init_page(){
    console.log('初始化 Tabs');
    // 初始化 Tabs
    new CustomTabs();
}
function test(){
    console.log('test');
}

</script>
