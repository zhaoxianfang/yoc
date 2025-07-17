<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 爬虫任务
        // rules 规则里面的字段键名 同 采集后写入的表 articles 字段一致，例如只采集某文章的内容和作者字段，则配置['content'=>'xpath','author'=>'xpath']
        Schema::create('spider_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('timer')->nullable()->index()->comment('主任务爬取的时间周期,例如:1 0 * * *');
            $table->string('name')->nullable()->comment('目标站点名称');
            $table->string('url')->nullable()->comment('采集目标站点url链接:例:"https://www.gov.cn/....htm"');
            $table->tinyInteger('type')->default(1)->index()->comment('采集目标类型;属于「文章」类型的采集结果才会记录到文章表;1文章正文，2文章列表,3报刊,4其他');
            $table->tinyInteger('url_can_repeated')->default(0)->index()->comment('采集url是否可以重复采集;1能，0不能; 例如有些站点主页或文章列表页的url是固定的就可以重复采集,文章详情页地址是唯一的就不能重复采集');
            $table->json('rules')->nullable()->comment('采集规则;格式["title"=>"xpath or css选择器","content"=>["field_handle"=>1,"route"=>["规则列表"]],"publish_time"=>["规则列表"],...];例:xpath(/html/body/div[6]/div[1]/div[2]/div[2])、元素名称(.content,#content)');
            $table->unsignedBigInteger('next_tasks_id')->default(0)->index()->comment('此任务完成后 需要紧密跟随的下一步采集任务,例如采集到文章列表后，需要立即进入到文章正文页面进行内容采集');
            $table->tinyInteger('sub_tasks')->default(0)->index()->comment('是否子任务;1是0否;子任务由主任务来调度，一般不直接运行子任务');
            $table->string('domain_prefix')->default('')->comment('域名前缀；有些站点url不是完整url,需要拼接上域名前缀路径');
            $table->json('extend')->nullable()->comment('执行爬虫的扩展');
            $table->json('before')->nullable()->comment('采集前需要做的事');
            $table->json('after')->nullable()->comment('采集后需要做的事');
            $table->json('fail')->nullable()->comment('采集出错时触发的事件');
            $table->json('success')->nullable()->comment('采集完成后可触发的事件');
            $table->tinyInteger('run_status')->default(0)->index()->comment('采集状态；0未执行,1成功，2失败');
            $table->tinyInteger('status')->default(1)->index()->comment('任务状态；1正常，2关闭');
            $table->dateTime('run_at')->nullable()->comment('最近一次采集时间');
            $table->timestamps();
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `spider_tasks` comment '爬虫采集任务'");

        // 爬虫任务日志
        Schema::create('spider_tasks_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('spider_tasks_id')->index()->comment('爬虫任务ID');
            $table->string('url')->nullable()->comment('爬取单个url地址');
            $table->longText('content')->nullable()->comment('日志内容');
            $table->string('name')->nullable()->comment('目标站点名称');
            $table->tinyInteger('status')->default(0)->index()->comment('采集状态；1成功，2失败');
            $table->timestamps();
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `spider_tasks_logs` comment '爬虫采集任务日志'");

        // 爬虫任务采集成功的 links; 用于去重，防止重复采集
        Schema::create('spider_links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url')->unique()->comment('采集url');
            $table->timestamps();
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `spider_links` comment '爬虫任务采集成功的 links; 用于去重，防止重复采集'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spider_tasks');
        Schema::dropIfExists('spider_tasks_logs');
        Schema::dropIfExists('spider_links');
    }
};
