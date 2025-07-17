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
        Schema::create('system_configs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name', 30)->nullable()->index()->comment('变量名');
            $table->string('group', 30)->nullable()->index()->comment('分组');
            $table->string('title', 100)->nullable()->comment('变量标题');
            $table->string('tip', 100)->nullable()->comment('变量描述');
            $table->string('type', 30)->nullable()->comment('类型:string,text,int,bool,array,datetime,date,file');
            $table->string('visible')->nullable()->comment('可见条件');
            $table->text('value')->nullable()->comment('变量值');
            $table->text('content', 60)->nullable()->comment('变量字典数据');
            $table->string('rule', 100)->nullable()->comment('验证规则');
            $table->string('extend')->nullable()->comment('扩展属性');
            $table->string('setting')->nullable()->comment('配置');
            $table->timestamps();
            // 联合唯一
            $table->unique(['name', 'group']);
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `system_configs` comment '系统配置'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_config');
    }
};
