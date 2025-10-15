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
        Schema::create('links', function (Blueprint $table) {
            $table->id(); // $table->bigIncrements('id');
            $table->string('short_code', 30)->unique()->index()->comment('短码:短链接编码,eg:abcde,全局唯一');
            $table->text('url')->comment('原始长链接');
            $table->string('type')->default('url')->comment('链接类型 url:链接,qr:二维码,phone:手机,email:邮箱,article:文章,file:文件,pay:支付地址,alipay:支付宝,baidu:百度,wechat:微信,tencent:腾讯,app:APP,other:其他');
            $table->dateTime('expire_at')->nullable()->comment('过期时间，可选，用于设置短链接有效期');
            $table->unsignedInteger('click_count')->default(0)->comment('点击次数');
            $table->unsignedTinyInteger('is_active')->default(1)->comment('是否激活，0=禁用，1=启用，用于软删除或停用');
            $table->unsignedBigInteger('user_id')->default(0)->index()->comment('创建用户ID');
            $table->string('remark')->nullable()->comment('备注');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
