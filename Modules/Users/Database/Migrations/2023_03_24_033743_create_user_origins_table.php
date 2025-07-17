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
        Schema::create('user_origins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable()->index()->comment('用户id');
            $table->string('open_id')->unique()->comment('标识（手机号 邮箱 用户名或第三方应用的唯一标识）');
            $table->string('source', 20)->nullable()->index()->comment('用户来源');

            $table->string('nickname', 30)->nullable()->comment('昵称');
            $table->unsignedTinyInteger('gender')->default(0)->comment('性别：0未设置，1男，2女');
            $table->string('cover')->nullable()->index()->comment('头像');
            $table->boolean('verified')->default(false)->comment('是否已经验证');
            $table->dateTime('authorized_at')->nullable()->comment('授权时间（获取用户信息）');
            $table->dateTime('login_at')->nullable()->comment('最近登录时间');

            $table->string('country', 20)->nullable()->comment('国家');
            $table->string('province', 50)->nullable()->comment('省份');
            $table->string('city', 50)->nullable()->comment('市');
            $table->string('county', 50)->nullable()->comment('县区');
            $table->string('town', 50)->nullable()->comment('乡镇');
            $table->string('village', 50)->nullable()->comment('村');

            $table->timestamps();
            $table->string('unionid')->nullable()->index();
            $table->json('all')->nullable()->comment('第三方回调的完整数据');
            $table->json('user_info')->nullable()->comment('授权获取的用户信息');
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `user_origins` comment '用户注册来源'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_origins');
    }
};
