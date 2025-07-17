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
        Schema::create('app_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index()->comment('创建用户ID');
            $table->string('platform')->default('android')->index()->comment('app 操作系统：（android || ios）');
            $table->string('version')->nullable()->comment('app 版本号，例如：1.0.0');
            $table->string('version_num')->nullable()->index()->comment('app 版本号数值，例如 100');
            $table->string('url')->nullable()->comment('下载地址');
            $table->string('note')->nullable()->comment('更新内容;例如：新增了如下功能<br />1、...<br />2、...');
            $table->unsignedTinyInteger('silent')->default(0)->index()->comment('是否静默更新【0否，1是】');
            $table->unsignedTinyInteger('force')->default(0)->index()->comment('是否强制更新【0否，1是】');
            $table->unsignedTinyInteger('net_check')->default(1)->index()->comment('非WIfi是否提示更新【0否，1是】');
            $table->unsignedTinyInteger('is_wgt')->default(0)->index()->comment('是否为wgt(热更新)包:1是(大版本相同直接安装):0否(apk主包,大版本不同时候使用)');
            $table->unsignedTinyInteger('status')->default(1)->index()->comment('是否启用【0否，1是】');
            $table->timestamps();
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `app_versions` comment 'App版本管理'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_versions');
    }
};
