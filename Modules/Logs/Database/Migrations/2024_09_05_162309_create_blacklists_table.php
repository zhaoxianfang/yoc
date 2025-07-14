<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blacklists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->default(0)->index()->comment('用户id');
            $table->ipAddress('ip')->unique()->comment('黑名单ip(疑似黑名单)');
            $table->string('remark')->nullable()->comment('备注');
            $table->integer('visits')->default(1)->comment('访问次数');
            $table->integer('type')->default(0)->comment('类型：0:疑似非法请求(观测中) 1:非法请求(拦截请求) ');
            $table->dateTime('ban_deadline')->nullable()->comment('封号截止时间；在此时间前拦截访问，为空表示不拦截');
            $table->float('ban_duration')->default(0)->comment('封号时长，单位：小时; 可根据此时长增加给出下次的封号时长');
            $table->integer('ban_number')->default(1)->comment('触发封号次数');
            $table->timestamps();
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `blacklists` comment '黑名单管理'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklists');
    }
};
