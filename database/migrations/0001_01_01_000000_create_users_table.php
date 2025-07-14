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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid', 30)->nullable()->index();
            $table->string('real_name', 20)->nullable()->comment('真实姓名');
            $table->string('nickname', 25)->nullable()->comment('昵称');
            $table->unsignedTinyInteger('gender')->default(0)->comment('性别：0未设置，1男，2女');
            $table->string('mobile', 15)->unique()->nullable();
            $table->timestamp('mobile_verified_at')->nullable()->comment('手机号认证时间');
            $table->string('cover')->nullable()->comment('用户上传到平台的头像');
            $table->string('email', 30)->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable()->comment('邮箱认证时间');
            $table->string('id_card', 20)->nullable()->unique()->comment('身份证号');
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->unsignedTinyInteger('status')->default(1)->index()->comment('状态：0未激活，1正常，2冻结');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
