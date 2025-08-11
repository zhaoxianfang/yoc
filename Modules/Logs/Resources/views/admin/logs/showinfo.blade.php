<style>
    body{background-color: #000;color:#fff;height: 100%;min-height:100%;padding: 0;overflow: hidden;scrollbar-width: none;}
    .log-box{width: 100vw;height: 100vh;overflow:auto;scrollbar-width: none;}
    .log-sub-title{width: 160px;text-align: right;display: inline-block;padding-right: 4px;}
</style>
<div class="log-box">
	<pre>
		<code>
【<span class="log-sub-title">请求方式 method</span>】:<span>  {{$info['method']}}</span>
【<span class="log-sub-title">日志标题 title</span>】:<span>  {{$info['title']}}</span>
【<span class="log-sub-title">日志内容 content</span>】:<span>  {{ show_json($info['content']) }}</span>
【<span class="log-sub-title">日志 id</span>】:<span>  {{$info['id']}}</span>
【<span class="log-sub-title">日志操作人 user_id</span>】:<span>  {{$info['user_id']}}</span>
【<span class="log-sub-title">用户 account</span>】:<span>  {{$info['user']?$info['user']['nickname']:''}}</span>
【<span class="log-sub-title">创建时间 created_at</span>】:<span>  {{$info['created_at']}}</span>
【<span class="log-sub-title">用户 ip</span>】:<span>  {{$info['source_ip']}}</span>
【<span class="log-sub-title">链接地址 url</span>】:<span>  {{ $info['url'] }}</span>
【<span class="log-sub-title">用标识 user_agent</span>】:<span>  {{$info['user_agent']}}</span>
【<span class="log-sub-title">模块 module_name</span>】:<span>  {{$info['module_name']}}</span>
【<span class="log-sub-title">是否为爬虫 is_crawler</span>】:<span>  {{$info['is_crawler']?'是':'否'}}</span>
【<span class="log-sub-title">爬虫名称 crawler_name</span>】:<span>  {{ $info['extra']?$info['extra']['crawler_name']:'' }}</span>
		</code>
	</pre>
</div>
