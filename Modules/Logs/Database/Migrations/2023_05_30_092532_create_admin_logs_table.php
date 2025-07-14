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
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_id')->index()->default(0)->comment('触发的管理员ID(0表示系统)');
            $table->unsignedBigInteger('admin_group_id')->nullable()->index()->comment('关联管理员角色组ID');
            $table->string('module_name')->nullable()->comment('日志发生的模块名称');
            $table->string('title')->nullable()->comment('日志标题');
            $table->longText('context')->nullable()->comment('提示内容');
            $table->json('extra')->nullable()->comment('附加数据');
            $table->string('source_ip')->nullable()->comment('触发者ip');
            $table->tinyInteger('is_crawler')->default(0)->index()->comment('是否为爬虫');
            $table->string('user_agent')->nullable()->comment('userAgent');
            $table->string('level', 20)->index()->default('notice')->comment('日志级别[error:系统异常;warning:警告;notice:普通提示;lowest:最低级别]');
            $table->timestamps();
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `admin_logs` comment '管理员日志'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_logs');
    }
};
