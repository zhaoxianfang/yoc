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
        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index()->default(0)->comment('创建用户ID,0表示系统爬虫');
            $table->unsignedBigInteger('classify_id')->index()->default(0)->comment('文章分类ID');
            $table->string('title')->comment('标题');
            $table->longText('content')->comment('内容');
            $table->text('summary')->nullable()->comment('摘要');
            $table->string('author')->nullable()->comment('作者/编辑 该字段主要给「爬虫」使用');
            $table->string('publish_time', 30)->nullable()->comment('发布时间 该字段主要给「爬虫」使用');
            $table->integer('sort')->default(0)->comment('排序；值越大越靠前');
            $table->tinyInteger('type')->default(1)->index()->comment('文章内容类型；1：富文本，2：Markdown');
            $table->integer('read')->default(0)->comment('被查看次数');
            $table->integer('like')->default(0)->comment('被点赞次数');
            $table->integer('spider')->default(0)->comment('被爬虫爬取次数');
            $table->smallInteger('source_type')->default(1)->index()->comment('文章来源类型；1：用户发布，2:爬虫采集');
            $table->string('source_url')->nullable()->comment('文章来源url');
            $table->timestamps();
            $table->tinyInteger('status')->default(1)->index()->comment('开放状态；0：待审，1：正常，2:不公开，3:敏感待审核');

            if (\DB::getDriverName() !== 'sqlite') {
                $table->fullText('title');
                $table->fullText('content');
                $table->fullText(['title', 'content']);
            }
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `articles` comment '文章'");

        Schema::create('article_classifies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_id')->index()->default(0)->comment('创建的管理员ID');
            $table->unsignedBigInteger('pid')->index()->default(0)->comment('父级id');
            $table->string('name', 50)->comment('名称');
            $table->integer('sort')->default(0)->comment('排序；值越大越靠前');
            $table->smallInteger('type')->default(1)->index()->comment('分类类型；1：用户发布，2:爬虫采集');
            $table->smallInteger('level')->default(1)->index()->comment('分类层级；tree 层级');
            $table->timestamps();
            $table->tinyInteger('show_nav')->default(2)->index()->comment('nav导航展示; 0:不展示；1仅移动端(app);2仅后台;3都展示');
            $table->tinyInteger('status')->default(1)->index()->comment('开放状态；0：待审，1：正常，2:不公开，3:敏感待审核');
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `article_classifies` comment '文章分类'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
        Schema::dropIfExists('article_classifies');
    }
};
