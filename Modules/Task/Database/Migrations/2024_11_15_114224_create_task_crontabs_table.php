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
        Schema::create('task_cron_tabs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->default(0)->index()->comment('父级任务id');
            $table->string('timer')->nullable()->index()->comment('主任务爬取的时间周期,例如:1 0 * * *');
            $table->string('name')->nullable()->comment('任务名称');

            $table->string('type', 10)->default('model')->index()->comment('任务类型: model 调用多态模型 ; func 调用类或方法');
            // modal
            $table->string('executable_id')->nullable()->comment('执行对象id:多态关联[模型]类型');
            $table->string('executable_type')->nullable()->comment('执行对象模型:多态关联[模型]类型');
            // func
            $table->string('execute_class_or_func')->nullable()->comment('被执行对象的类或者方法');
            $table->string('class_or_func_params')->nullable()->comment('执行类或者方法的参数: json 字符串格式');
            // curl
            $table->string('curl_url')->nullable()->comment('Http 请求地址');
            $table->json('curl_params')->nullable()->comment('Http 请求参数');

            $table->tinyInteger('run_status')->default(0)->index()->comment('任务执行状态；0未执行,1成功，2失败');
            $table->tinyInteger('status')->default(1)->index()->comment('任务状态；1正常，2关闭');
            $table->dateTime('run_at')->nullable()->comment('最近一次运行时间');

            $table->timestamps();
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `task_cron_tabs` comment '定时任务'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_crontab');
    }
};
