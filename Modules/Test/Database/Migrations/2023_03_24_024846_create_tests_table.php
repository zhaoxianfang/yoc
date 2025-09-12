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
        Schema::create('tests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable()->comment('日志标题');
            $table->longText('content')->nullable()->comment('提示内容');
            $table->json('object')->nullable()->comment('object');
            $table->timestamps();
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `tests` comment '测试'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
