<style>
    .toc{display: none!important;}
    .app-container{grid-template-columns: 220px 1fr 10px!important;}
</style>


<div class="row bg-white">
    <div class="col-12">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>提示： <small>填写并提交下面的表单，即可创建一个应用文档.</small></h5>
                <div class="ibox-tools"></div>
            </div>

            <div class="ibox-content">
                <form method="post" class="p-3 unbind-form" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">文档名称<font color="#FF0000">*</font></label>
                        <div class="col-sm-10"><input type="text" class="form-control" name="app_name" value="{{ old("app_name",$docs_app?->app_name) }}" data-rule="required" /></div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group row"><label class="col-sm-2 col-form-label">接口域名</label>
                        <div class="col-sm-10">

                            @if (!empty(old('urls',$docs_app?->urls)))
                                @php
                                    $alias = old('urls',(array)$docs_app?->urls)['alias'];
                                    $urlPrefix = old('urls',(array)$docs_app?->urls)['url_prefix'];
                                @endphp
                                @foreach ($alias as $alias_key => $alias_value)
                                    <div class="row url_item" style="margin:0;">
                                        <input type="text" class="form-control col-sm-3" id="url_base_alias" value="{{ $alias_value??'' }}" name="urls[alias][]" placeholder="例如:测试环境" autocomplete="off" data-tips title="url前缀 别名 例如：测试环境" />
                                        <input type="text" class="form-control col-sm-7" id="url_base" value="{{ $urlPrefix[$alias_key]??'' }}" name="urls[url_prefix][]" placeholder="例如:http://api.xxx.com/" autocomplete="off" data-tips title="以 http(s)开头，'/'结尾的url地址，例如：http://api.xxx.com/" />
                                        <div class="col-sm-2">
                                            @if($alias_key < 1)
                                                <button type="button" class="btn btn-sm btn-primary" id='plus_app_url' ><i>&#xFF0B;</i></button>
                                                <button type="button" class="btn btn-sm btn-danger trash_app_url" style="display: none;" ><i class="custom-trash"></i></button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-danger trash_app_url" ><i class="custom-trash"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="row url_item" style="margin:0;">
                                    <input type="text" class="form-control col-sm-3" id="url_base_alias" value="{{ old("urls")?old("urls")['alias']['0']:'' }}" name="urls[alias][]" placeholder="例如:测试环境" autocomplete="off" data-tips title="url前缀 别名 例如：测试环境" />
                                    <input type="text" class="form-control col-sm-7" id="url_base" value="" name="urls[url_prefix][]" placeholder="例如:http://api.xxx.com/" autocomplete="off" data-tips title="以 http(s)开头，'/'结尾的url地址，例如：http://api.xxx.com/" />
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-sm btn-primary" id='plus_app_url' ><i>&#xFF0B;</i></button>
                                        <button type="button" class="btn btn-sm btn-danger trash_app_url" style="display: none;" ><i class="custom-trash"></i></button>
                                    </div>
                                </div>
                            @endif

                            <div id="add_url_prefix"></div>
                            <span class="form-text m-b-none">该项主要用于API文档接口,若无则忽略本项.</span>
                        </div>

                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">封面图</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input id="location-app_cover" value="" class="form-control" onclick="$('#app_cover').click();" placeholder="未上传封面图的使用默认图" data-tips="" title="#app_cover_img_box">
                                <label class="input-group-btn">
                                    <input type="button" value="请选择上传图片" class="btn  btn-sm btn-primary upload-file-btn" onclick="$('#app_cover').click();">
                                </label>
                            </div>
                        </div>
                        <input type="file" name="app_cover" id='app_cover' value="" accept=".jpg, .jpeg, .png" onchange="$('#location-app_cover').val($('#app_cover').val());" style="display: none">

                        <div id="app_cover_img_box" style="display:none;">
                            <span>图片预览:155*200px</span>
                            <br />
                            <img src="/static/modules/docs/images/default_apps_cover.jpeg" id="app_cover_img" style="width: 155px;height: 200px;" alt="文档封面图">
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">文档简介<font color="#FF0000">*</font></label>
                        <div class="col-sm-10">
                            <textarea class="form-control" name="description" placeholder="介绍一下本文档..." rows="4" data-rule="required">{{ old("description",$docs_app?->description) }}</textarea>
                            <span class="form-text m-b-none">好的简介更容易体现文档的主题和核心</span>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">文档公开类型 <br/>
                            <small class="text-navy">默认公开</small>
                        </label>
                        <div class="col-sm-10">
                            <div>
                                <label>
                                    <input type="radio" @if(old("open_type",$docs_app?->open_type??1) == 1) checked @endif value="1" name="open_type">
                                    公开[有助于别人发现您的文档][所有人可见]
                                </label>
                            </div>
                            <div>
                                <label>
                                    <input type="radio" @if(old("open_type",$docs_app?->open_type??1) == 2) checked @endif  value="2" name="open_type">
                                    仅添加到本文档的成员可见[内部文档]
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">文档标记时间 <br/>
                            <small class="text-navy">标记在指定天数内被修改的文档</small>
                        </label>
                        <div class="col-sm-10">
                            <select class="form-control m-b" name="mark_days">
                                <option value="1" @if(old("mark_days",$docs_app?->mark_days??3) == 1) selected @endif>1天</option>
                                <option value="2" @if(old("mark_days",$docs_app?->mark_days??3) == 2) selected @endif>2天</option>
                                <option value="3" @if(old("mark_days",$docs_app?->mark_days??3) == 3) selected @endif>3天[推荐]</option>
                                <option value="4" @if(old("mark_days",$docs_app?->mark_days??3) == 4) selected @endif>4天</option>
                                <option value="5" @if(old("mark_days",$docs_app?->mark_days??3) == 5) selected @endif>5天</option>
                                <option value="7" @if(old("mark_days",$docs_app?->mark_days??3) == 7) selected @endif>一周</option>
                                <option value="10" @if(old("mark_days",$docs_app?->mark_days??3) == 10) selected @endif>10天</option>
                                <option value="15" @if(old("mark_days",$docs_app?->mark_days??3) == 15) selected @endif>半个月</option>
                                <option value="30" @if(old("mark_days",$docs_app?->mark_days??3) == 30) selected @endif>一个月</option>
                            </select>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group  row">
                        <label class="col-sm-2 col-form-label">团队名称/创作者<font color="#FF0000">*</font></label>
                        <div class="col-sm-10"><input type="text" name="team_name" class="form-control" value="{{ old("team_name",$docs_app?->team_name??'') }}" data-rule="required"/></div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group  row">
                        <label class="col-sm-2 col-form-label">文档标签 <br/>
                            <small class="text-navy">在文档列表中展示</small>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" name="tag" class="form-control" value="{{ old("tag",$docs_app?->tag??'') }}" data-rule="max:2" maxlength="2"/>
                            <span class="form-text m-b-none">最多2个字</span>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group row"><label class="col-sm-2 col-form-label">文档状态</label>
                        <div class="col-sm-10">
                            <label> <input type="radio" name="status" value="1" @if(old("status",$docs_app?->status??1) == 1) checked @endif> 正常 </label>
                            <label> <input type="radio" name="status" value="0" @if(old("status",$docs_app?->status??1) == 0) checked @endif> 停用[仅文档创建人可见] </label>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group row">
                        <div class="col-sm-10 offset-sm-2">
                            <button type="reset" class="btn btn-sm btn-w-m btn-white">取消</button>
                            <button type="submit" class="btn btn-sm btn-w-m btn-primary">提交</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    // 初始化URL管理功能
    function initUrlHandlers() {
        // 移除并重新绑定删除按钮事件
        document.querySelectorAll('.trash_app_url').forEach(btn => {
            const oldId = btn.id;
            btn.replaceWith(btn.cloneNode(true));
            const newBtn = oldId ? document.getElementById(oldId) :
                document.querySelector('.trash_app_url');
            newBtn.onclick = () => newBtn.closest('.url_item')?.remove();
        });

        // 处理添加按钮
        const plusBtn = document.getElementById('plus_app_url');
        if (plusBtn) {
            plusBtn.replaceWith(plusBtn.cloneNode(true));
            const newPlusBtn = document.getElementById('plus_app_url');

            newPlusBtn.onclick = () => {
                const template = document.querySelector('.url_item');
                if (!template) return;

                const clone = template.cloneNode(true);
                clone.querySelectorAll('input').forEach(input => input.value = '');
                const trashBtn = clone.querySelector('.trash_app_url');
                if (trashBtn) trashBtn.style.display = '';
                clone.querySelector('#plus_app_url')?.remove();

                const addPrefix = document.getElementById('add_url_prefix');
                if (addPrefix) addPrefix.before(clone);

                // 只需为新删除按钮绑定事件
                clone.querySelector('.trash_app_url').onclick = () => clone.remove();
            };
        }
    }
    // 在每个 guide 页面都都定义一个 init_page 函数，来初始化页面事件
    function init_page(){
        listenFileChangeURL('#app_cover',function (src,ele) {
            console.log(src);
            // 直接使用DOM API
            document.querySelector('#app_cover_img').src = src;
        });

        initUrlHandlers();
        init_tips();
    }

    document.addEventListener('DOMContentLoaded', function() {
        init_page();
    });

</script>
