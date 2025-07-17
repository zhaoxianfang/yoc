@foreach ($page_users as $user)
    <div class="no-gutters col-12 col-lg-3 col-md-4 col-sm-4">
        <div class="book-card">
            <div class="book-content-wrapper">
                <img src="{{ $user['cover'] }}" alt="" class="book-card-img">
                <div class="book-content">
                    <div class="book-name">
                        昵称:{{ $user['nickname'] }}
                    </div>
                    @if(!empty($user['extra_nickname']))
                        @can('update', $docs_app)
                            <div class="book-sum">
                                备注: {{ $user['extra_nickname'] }}
                            </div>
                        @endcan
                    @endif
                    @if(!empty($user['status']))
                        <div class="book-sum">
                            角色:<button type="button" class="btn btn-mini btn-outline-success">{{ $user['user_role_text']??'-' }}</button>
                        </div>
                        @if($user['user_status'] != 1)
                        <div class="book-sum">
                            审核状态:
                            @if($user['user_status'] < 1)
                                <button type="button" class="btn btn-mini btn-outline-info">{{ $user['user_status_text']??'-' }}</button>
                            @else
                                <button type="button" class="btn btn-mini btn-outline-danger">{{ $user['user_status_text']??'-' }}</button>
                            @endif
                        </div>
                        @endif
                    @endif
                </div>
            </div>
            @can('update', $docs_app)
                <div class="book-users">
                    @if(!empty($user['status']) && !empty($user['user_role']))
                        @if($user['user_role'] == 9 || ($user['user_role'] >= 7 && !$docs_app->isSuper()))
                            <button type="button" class="btn btn-mini btn-white">不可操作</button>
                        @else
                            <button type="button" class="btn btn-mini btn-outline-success docs_app_user_set_rule" data-nickname="{{ $user['nickname'] }}" data-extra_nickname="{{ $user['extra_nickname'] }}" data-appid="{{$docs_app['id']}}" data-userid="{{ $user['id'] }}">赋权</button>
                            <button type="button" class="btn btn-mini btn-outline-danger docs_app_user_kick_out" data-nickname="{{ $user['nickname'] }}" data-extra_nickname="{{ $user['extra_nickname'] }}" data-appid="{{$docs_app['id']}}" data-userid="{{ $user['id'] }}">踢出</button>
                        @endif
                    @else
                        <button type="button" class="btn btn-mini btn-outline-success docs_app_user_allow_join" data-nickname="{{ $user['nickname'] }}" data-extra_nickname="{{ $user['extra_nickname'] }}" data-appid="{{$docs_app['id']}}" data-userid="{{ $user['id'] }}">同意加入</button>
                        <button type="button" class="btn btn-mini btn-outline-danger docs_app_user_refuse" data-nickname="{{ $user['nickname'] }}" data-extra_nickname="{{ $user['extra_nickname'] }}" data-appid="{{$docs_app['id']}}" data-userid="{{ $user['id'] }}">驳回申请</button>
                    @endif
                </div>
            @endcan
        </div>
    </div>
@endforeach
@empty($page_users)
    <div class="text-center">
        暂无数据
    </div>
@endempty
