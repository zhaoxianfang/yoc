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
        Schema::create('crawlers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('module_name')->nullable()->comment('日志发生的模块名称');
            $table->string('name')->index()->nullable()->comment('爬虫名称');
            $table->string('url')->nullable()->comment('爬虫访问的url地址');
            $table->json('headers')->nullable()->comment('请求携带的头信息');
            $table->json('params')->nullable()->comment('请求携带的数据');
            $table->string('source_ip')->nullable()->comment('触发者ip');
            $table->string('user_agent')->nullable()->comment('userAgent');
            $table->timestamps();
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `crawlers` comment '爬虫日志'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawlers');
    }
};
