@extends('admin::layouts.admin_layout')
@section('title', '系统配置')

@section('content')
    <div class="row col-12">
        <div class="card">
            <div class="card-header">
                <div class="flex-grow-1">
                    <h4 class="card-title">系统配置</h4>
                </div>
            </div>
            <form method="post" class="bg-white-X">
                @csrf
                <div class="card-body">
                <p class="text-muted">
                    操作有分险,请谨慎操作！
                </p>

                <ul class="nav nav-tabs mb-2">
                    <li class="nav-item">
                        <a href="#tab-common" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">公共配置</a>
                    </li>
                    <li class="nav-item">
                        <a href="#tab-email" data-bs-toggle="tab" aria-expanded="false" class="nav-link">邮件配置</a>
                    </li>
                    @foreach ($tabs as $tab)
                        @if (!$loop->first)

                    <li class="nav-item">
                        <a href="#tab-{{ $tab['value']['key'] }}" data-bs-toggle="tab" aria-expanded="false" class="nav-link">{{ $tab['value']['name'] }}</a>
                    </li>

                        @endif
                    @endforeach

                    <li class="nav-item">
                        <a href="#tab-config" data-bs-toggle="tab" aria-expanded="false" class="nav-link"> Tabs配置 </a>
                    </li>
                    @if ( $admin->checkAuth('system/config/create') )

                    <li class="nav-item">
                        <a href="#tab-plus" data-bs-toggle="tab" aria-expanded="false" class="nav-link"><span class="m-lg-1"><i class="ti ti-plus fs-xl"></i>新增</span></a>
                    </li>
                    @endif
                </ul>

                <div class="tab-content">
                    @foreach ($tabs as $tab)
                    <div class="tab-pane {{ $tab['value']['key'] == 'common' ? 'show active':''  }}" id="tab-{{ $tab['value']['key'] }}">
                        <div class="panel-body">

                            @php($groupName = $tab['value']['key'])

                            @isset($$groupName)

                                @foreach ($$groupName as $group)
                                    @switch($group['type'])
                                        @case('string')
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">{{$group['title']}}</label>
                                                <div class="col-sm-7">
                                                    <input type="text" class="form-control" name="config[{{$group['group']}}][{{$group['name']}}]" value="{{$group['value']??''}}" placeholder="" data-rule="{{$group['rule']??''}}">
                                                    <span class="form-text m-b-none">提示:{{$group['tip']??''}}</span>
                                                </div>
                                                <label class="col-sm-3 col-form-label">setting('{{$group['group'].'.'.$group['name']}}')</label>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            @break

                                        @case('password')
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">{{$group['title']}}</label>
                                                <div class="col-sm-7">
                                                    <input type="password" class="form-control" name="config[{{$group['group']}}][{{$group['name']}}]" value="{{$group['value']??''}}" placeholder="" data-rule="{{$group['rule']??''}}">
                                                    <span class="form-text m-b-none">提示:{{$group['tip']??''}}</span>
                                                </div>
                                                <label class="col-sm-3 col-form-label">setting('{{$group['group'].'.'.$group['name']}}')</label>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            @break

                                        @case('text')
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">{{$group['title']}}</label>
                                                <div class="col-sm-7">
                                                    <textarea class="form-control" name="config[{{$group['group']}}][{{$group['name']}}]" placeholder="" cols="30" rows="5" data-rule="{{$group['rule']??''}}">{{$group['value']??''}}</textarea>
                                                    <span class="form-text m-b-none">提示:{{$group['tip']??''}}</span>
                                                </div>
                                                <label class="col-sm-3 col-form-label">setting('{{$group['group'].'.'.$group['name']}}')</label>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            @break

                                        @case('number')
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">{{$group['title']}}</label>
                                                <div class="col-sm-7">
                                                    <input type="number" name="config[{{$group['group']}}][{{$group['name']}}]" value="{{$group['value']??''}}" placeholder="" class="form-control" data-rule="{{$group['rule']??''}}">
                                                    <span class="form-text m-b-none">提示:{{$group['tip']??''}}</span>
                                                </div>
                                                <label class="col-sm-3 col-form-label">setting('{{$group['group'].'.'.$group['name']}}')</label>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            @break

                                        @case('date')
                                            <div class="form-group row datepicker-date">
                                                <label class="col-sm-2 col-form-label">{{$group['title']}}</label>
                                                <div class="col-sm-7">
                                                    <div class="input-group m-b date">
                                                        <input type="text" class="form-control" name="config[{{$group['group']}}][{{$group['name']}}]" value="{{$group['value']??''}}"  data-rule="{{$group['rule']??''}}">
                                                        <div class="input-group-append">
                                                            <span class="input-group-addon"><i class="ti ti-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                    <span class="form-text m-b-none">提示:{{$group['tip']??''}}</span>
                                                </div>
                                                <label class="col-sm-3 col-form-label">setting('{{$group['group'].'.'.$group['name']}}')</label>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            @break

                                        @case('date_y')
                                            <div class="form-group row datepicker-date_y">
                                                <label class="col-sm-2 col-form-label">{{$group['title']}}</label>
                                                <div class="col-sm-7">
                                                    <div class="input-group m-b date">
                                                        <input type="text" class="form-control" name="config[{{$group['group']}}][{{$group['name']}}]" value="{{$group['value']??''}}"  data-rule="{{$group['rule']??''}}">
                                                        <div class="input-group-append">
                                                            <span class="input-group-addon"><i class="ti ti-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                    <span class="form-text m-b-none">提示:{{$group['tip']??''}}</span>
                                                </div>
                                                <label class="col-sm-3 col-form-label">setting('{{$group['group'].'.'.$group['name']}}')</label>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            @break

                                        @case('date_y_m')
                                            <div class="form-group row datepicker-date_y_m">
                                                <label class="col-sm-2 col-form-label">{{$group['title']}}</label>
                                                <div class="col-sm-7">
                                                    <div class="input-group m-b date">
                                                        <input type="text" class="form-control" name="config[{{$group['group']}}][{{$group['name']}}]" value="{{$group['value']??''}}"  data-rule="{{$group['rule']??''}}">
                                                        <div class="input-group-append">
                                                            <span class="input-group-addon"><i class="ti ti-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                    <span class="form-text m-b-none">提示:{{$group['tip']??''}}</span>
                                                </div>
                                                <label class="col-sm-3 col-form-label">setting('{{$group['group'].'.'.$group['name']}}')</label>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            @break

                                        @case('time')
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">{{$group['title']}}</label>
                                                <div class="col-sm-7">
                                                    <div class="input-group clockpicker" data-autoclose="true">
                                                        <input type="text" class="form-control" name="config[{{$group['group']}}][{{$group['name']}}]" value="{{$group['value']??''}}"  data-rule="{{$group['rule']??''}}">
                                                        <div class="input-group-append">
                                                            <span class="input-group-addon"><span class="ti ti-clock-o"></span></span>
                                                        </div>
                                                    </div>
                                                    <span class="form-text m-b-none">提示:{{$group['tip']??''}}</span>
                                                </div>
                                                <label class="col-sm-3 col-form-label">setting('{{$group['group'].'.'.$group['name']}}')</label>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            @break

                                        @case('daterange')
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">{{$group['title']}}</label>
                                                <div class="col-sm-7">
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control" name="config[{{$group['group']}}][{{$group['name']}}][start]" value="{{($group['value'] && $group['value']['start'])?$group['value']['start']:''}}"  data-rule="{{$group['rule']??''}}">
                                                        <span class="input-group-addon">to</span>
                                                        <input type="text" class="form-control form-control" name="config[{{$group['group']}}][{{$group['name']}}][end]" value="{{($group['value'] && $group['value']['end'])?$group['value']['end']:''}}" data-rule="{{$group['rule']??''}}"/>
                                                    </div>
                                                    <span class="form-text m-b-none">提示:{{$group['tip']??''}}</span>
                                                </div>
                                                <label class="col-sm-3 col-form-label">setting('{{$group['group'].'.'.$group['name']}}')</label>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            @break

                                        @case('select')
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">{{$group['title']}}</label>
                                                <div class="col-sm-7">
                                                    <select class="form-control m-b select2 custom-select" name="config[{{$group['group']}}][{{$group['name']}}]" data-rule="{{$group['rule']??''}}">
                                                        <option value="">无</option>
                                                        @foreach ($group['content'] as $value => $title)
                                                            <option value="{{ $value??'' }}" @if ($value == $group['value']??'') selected @endif>{{ $title }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="form-text m-b-none">提示:{{$group['tip']??''}}</span>

                                                </div>
                                                <label class="col-sm-3 col-form-label">setting('{{$group['group'].'.'.$group['name']}}')</label>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            @break

                                        @case('selects')
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">{{$group['title']}}</label>
                                                <div class="col-sm-7">
                                                    <select class="form-control m-b select2 custom-select" name="config[{{$group['group']}}][{{$group['name']}}][]" multiple="multiple" data-rule="{{$group['rule']??''}}">
                                                        <option value="">无</option>
                                                        @foreach ($group['content'] as $value => $title)
                                                            <option value="{{ $value??'' }}" @if (in_array($value,(array)$group['value'])) selected @endif>{{ $title }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="form-text m-b-none">提示:{{$group['tip']??''}}</span>
                                                </div>
                                                <label class="col-sm-3 col-form-label">setting('{{$group['group'].'.'.$group['name']}}')</label>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            @break

                                        @case('checkbox')
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">{{$group['title']}}</label>
                                                <div class="col-sm-7">
                                                    @foreach ($group['content'] as $value => $title)
                                                        <div class="i-checks">
                                                            <label> <input type="checkbox" name="config[{{$group['group']}}][{{$group['name']}}][]" value="{{$value??''}}"  data-rule="{{$group['rule']??''}}" @if (in_array($value,(array)$group['value'])) checked @endif> <i></i> {{ $title }} </label>
                                                        </div>
                                                    @endforeach
                                                    <span class="form-text m-b-none">提示:{{$group['tip']??''}}</span>
                                                </div>
                                                <label class="col-sm-3 col-form-label">setting('{{$group['group'].'.'.$group['name']}}')</label>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            @break

                                        @case('radio')
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">{{$group['title']}}</label>
                                                <div class="col-sm-7">
                                                    @foreach ($group['content'] as $value => $title)
                                                        <div class="i-checks">
                                                            <label> <input type="radio" name="config[{{$group['group']}}][{{$group['name']}}]" value="{{$value??''}}"  data-rule="{{$group['rule']??''}}" @if ($value == $group['value']) checked @endif> <i></i> {{ $title }} </label>
                                                        </div>
                                                    @endforeach
                                                    <span class="form-text m-b-none">提示:{{$group['tip']??''}}</span>
                                                </div>
                                                <label class="col-sm-3 col-form-label">setting('{{$group['group'].'.'.$group['name']}}')</label>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            @break

                                        @default
                                            {{-- 其他不识别的类型 --}}
                                    @endswitch
                                @endforeach

                            @endisset
                        </div>
                    </div>
                    @endforeach

                    <div class="tab-pane" id="tab-email">
                        <div class="panel-body">
                            <strong>系统邮件配置</strong>

                            <p>可以配置多个邮件账号，当一个邮件账号异常时候可以使用另一个邮箱发送系统邮件 </p>

                            <div class="hr-line-dashed"></div>

                            <div class="row">
                                @foreach ($email as $mail_key => $mail)
                                    <div class="col-sm-{{12/(int)(empty($count = count($email))?1:$count) }} {{ ($mail_key<$count-1)?'b-r':'' }} ">
                                        <h3 class="m-t-none m-b">邮件「{{ $mail_key+1 }}」</h3>
                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label">服务地址</label>
                                            <div class="col-lg-10">
                                                <input type="text" name="config[email][host][]" value="{{!empty($mail['value'])?$mail['value']['host']:'smtp.qq.com'}}" placeholder="例如:smtp.qq.com" class="form-control">
                                                <span class="form-text m-b-none">服务地址 例如:smtp.qq.com</span>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label">TCP端口号</label>
                                            <div class="col-lg-10">
                                                <input type="number" name="config[email][port][]" value="{{!empty($mail['value'])?$mail['value']['port']:'465'}}" placeholder="例如:465" class="form-control">
                                                <span class="form-text m-b-none">TCP端口号 QQ邮箱使用465</span>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label">邮箱号</label>
                                            <div class="col-lg-10">
                                                <input type="email" name="config[email][mail][]" value="{{!empty($mail['value'])?$mail['value']['mail']:''}}" placeholder="Email" class="form-control">
                                                <span class="form-text m-b-none">登录邮箱的账号 例如:test@qq.com</span>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label">授权密码</label>
                                            <div class="col-lg-10">
                                                <input type="text" placeholder="授权密码" name="config[email][password][]" value="{{!empty($mail['value'])?$mail['value']['password']:''}}" class="form-control">
                                                <span class="form-text m-b-none">客户端授权密码，注意不是登录密码.</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="hr-line-dashed"></div>

                        </div>
                    </div>
                    <div class="tab-pane" id="tab-config">
                        <div class="panel-body">
                            <strong>Tabs字典配置</strong>

                            <p>为系统配置的Tab名称进行命名 </p>

                            <div class="hr-line-dashed"></div>


                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <div class="input-group row mb-3">
                                        <div class="input-group-prepend col-3 m-0 p-0">
                                            <input type="text" class="form-control border-0 font-bold" disabled placeholder="模块键名">
                                        </div>
                                        <input type="text" class="form-control border-0 font-bold col-4 m-0 p-0" disabled placeholder="中文名">

                                        <div class="input-group-append col-5 m-0 p-0">
                                            <input type="text" class="form-control border-0 font-bold" disabled placeholder="操作">
                                        </div>
                                    </div>

                                    <div class="input-group row mb-3 config-tabs-item" id="config_common_tabs">
                                        <div class="input-group-prepend col-3 m-0 mr-1 p-0 pl-2">
                                            <input type="text" class="form-control" name="config[tabs][key][]" value="common" disabled placeholder="模块键名(例:docs)">
                                        </div>

                                        <input type="text" class="form-control col-4 m-0 p-0 pl-2" name="config[tabs][name][]" value="公共配置" disabled placeholder="中文名(例:在线文档)">

                                        <div class="input-group-append col-5 m-0 pl-2">
                                            <button type="button" class="btn btn-outline btn-danger h-100" id="plus_tabs" style="border-radius: 0;"><i class="ti ti-plus"></i></button>
                                            <button type="button" class="btn btn-outline btn-danger trash_tabs h-100" style="border-radius: 0; display: none;"><i class="ti ti-trash"></i></button>
                                        </div>
                                    </div>

                                    <div class="input-group row mb-3 config-tabs-item">
                                        <div class="input-group-prepend col-3 m-0 mr-1 p-0 pl-2">
                                            <input type="text" class="form-control" name="config[tabs][key][]" value="email" disabled placeholder="模块键名(例:docs)">
                                        </div>

                                        <input type="text" class="form-control col-4 m-0 p-0 pl-2" name="config[tabs][name][]" value="邮件配置" disabled placeholder="中文名(例:在线文档)">

                                        <div class="input-group-append col-5 m-0 pl-2">
                                            <button type="button" class="btn btn-outline btn-danger h-100" style="border-radius: 0;background-color: #fff; color: #ec4758;" disabled><i class="ti ti-trash"></i></button>
                                        </div>
                                    </div>

                                    @foreach ($tabs as $tab)
                                        @if (!in_array($tab['value']['key'],['common','email']))
                                            <div class="input-group row mb-3 config-tabs-item">
                                                <div class="input-group-prepend col-3 m-0 mr-1 p-0 pl-2">
                                                    <input type="text" class="form-control" name="config[tabs][key][]" value="{{ !empty($tab['value'])?$tab['value']['key']:''}}" placeholder="模块键名(例:docs)">
                                                </div>

                                                <input type="text" class="form-control col-4 m-0 p-0 pl-2" name="config[tabs][name][]" value="{{!empty($tab['value'])?$tab['value']['name']:''}}"  placeholder="中文名(例:在线文档)">

                                                <div class="input-group-append col-5 m-0 pl-2">
                                                    <button type="button" class="btn btn-outline btn-danger trash_tabs" style="border-radius: 0;"><i class="ti ti-trash"></i></button>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    <div id="add_tabs_box"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if ( $admin->checkAuth('system/config/create') )
                    <div class="tab-pane" id="tab-plus">
                        <div class="panel-body">
                            <strong>新建配置项</strong>

                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">分组</label>
                                <div class="col-sm-10">
                                    <select class="form-control m-b custom-select" name="config[add_field][group]" id="add_group_select">
                                        <option value="common">公共配置</option>
                                        @foreach ($tabs as $tab)
                                            @if (!in_array($tab['value']['key'],['common','email']))
                                                <option value="{{ $tab['value']['key'] }}">{{ $tab['value']['name'] }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">字段类型</label>
                                <div class="col-sm-10">
                                    <select class="form-control m-b custom-select" name="config[add_field][type]">
                                        @foreach ($type_list as $type => $name)
                                            <option value="{{ $type }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">变量名</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="config[add_field][name]" data-join="check_plus_name" data-rule="remote(/admin/system/config/unique)">
                                    <span class="form-text m-b-none">提示:建议使用英文单词或包含下划线的字母，例如:system_name</span>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">变量标题</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="config[add_field][title]">
                                    <span class="form-text m-b-none">提示:建议使用易于阅读的汉字，例如：系统名称</span>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">变量字典候选值</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" name="config[add_field][content]" placeholder="使用竖线“|”分开的键值对数据，每行一对数据；&#13;&#10;例如：&#13;&#10;value1|title1&#13;&#10;value2|title2" cols="30" rows="5"></textarea>
                                    <span class="form-text m-b-none">提示:使用竖线“|”分开的键值对数据，每行一对数据.【做为 下拉(select),下拉多选(selects),复选(checkbox),单选(radio) 的候选项】</span>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">提示语</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="config[add_field][tip]">
                                    <span class="form-text m-b-none">提示:建议使用易于阅读的提示语，例如：请填写 字母和下划线 组成的 字符串！</span>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">表单规则</label>
                                <div class="col-sm-10">
                                    <select class="form-control m-b select2 custom-select" name="config[add_field][rule][]" multiple="multiple">
                                        <option value="">无</option>
                                        @foreach ($rule_list as $rule => $name)
                                            <option value="{{ $rule }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-text m-b-none">提示:做为提交表单前的数据验证规则</span>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">扩展</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" name="config[add_field][extend]" cols="30" rows="5"></textarea>
                                    <span class="form-text m-b-none">提示:暂未使用</span>
                                </div>
                            </div>

                        </div>
                    </div>
                    @endif
                </div>


            </div>
                <div class="card-footer text-left">
                    <div class="row">
                        <div class="col-2"></div>
                        <div class="col-8">
                            <button type="submit" class="btn btn-primary fw-semibold py-2 w-md-25 w-75">提交</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_js')
    <script>
        function check_plus_name(ele){
            return {
                'type':'add_system_config',
                'group':$('#add_group_select').val(),
                'name':$(ele).val()
            }
        }

        // =========================
        // 添加/删除tabs列表 begin
        $(".trash_tabs").off()
        $("#plus_tabs").off()
        $('.trash_tabs').click(function(){
            $(this).parent().parent('.config-tabs-item').remove();
        });
        $('#plus_tabs').click(function(){
            var child = $("#config_common_tabs").clone(true);
            //清除克隆的数据
            child.removeAttr("id")
            child.find('.trash_tabs').show();
            child.find('#plus_tabs').remove();
            child.find(":input").each(function(i){
                $(this).val("");
                $(this).removeAttr("disabled")
                $(this).removeAttr("readonly")
                $(this).removeAttr("id")
            });
            $("#add_tabs_box").before(child);
        });
        // 添加/删除tabs列表 end
        // =========================

        // 提交之后
        function form_after(resp){
            myTools.msg( resp.message || '操作成功')
        }
    </script>
@endsection
