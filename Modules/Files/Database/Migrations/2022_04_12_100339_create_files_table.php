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
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index()->comment('创建用户ID');
            $table->string('name')->nullable()->comment('文件名称');
            $table->string('ext', 10)->nullable()->comment('文件后缀格式');
            $table->string('original_name')->nullable()->comment('原文件名');
            $table->string('type')->nullable()->comment('文件的 Mime 类型');
            $table->unsignedBigInteger('size')->nullable()->comment('文件大小 单位：bit');
            $table->string('path')->nullable()->comment('文件保存地址');
            $table->string('driver', 15)->nullable()->comment('文件驱动');
            $table->unsignedTinyInteger('status')->default(0)->comment('上传文件后否启用【0否，1是】');
            $table->timestamps();
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `files` comment '上传文件记录管理'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
};
