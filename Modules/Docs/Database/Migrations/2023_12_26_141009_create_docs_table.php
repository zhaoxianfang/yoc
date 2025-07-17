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
        Schema::create('docs_apps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uni_code', 30)->nullable()->unique()->comment('应用唯一标识');
            $table->string('app_name', 60)->nullable()->comment('应用名称');
            $table->string('app_cover')->nullable()->comment('应用封面图');
            $table->json('urls')->nullable()->comment('应用接口可能用到的跳转地址:json格式');
            $table->string('description')->nullable()->comment('应用描述');
            $table->integer('sort')->default(0)->comment('排序；值越大越靠前');
            $table->tinyInteger('open_type')->nullable()->default(1)->index()->comment('应用公开类型；1全公开，2仅文档成员可见');
            $table->unsignedBigInteger('create_by')->default(0)->index()->comment('应用创建人');
            $table->string('theme', 20)->nullable()->default('default')->comment('应用主题风格');
            $table->smallInteger('mark_days')->default(7)->comment('标记多少天内修改的文档');
            $table->string('team_name')->nullable()->comment('团队名称/创作者');
            $table->string('tag', 10)->nullable()->comment('自定义标签（2个字以内）');
            $table->timestamps();
            $table->tinyInteger('status')->default(1)->index()->comment('应用状态；1正常，0停用');
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `docs_apps` comment '文档应用'");

        Schema::create('docs_app_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID');
            $table->unsignedBigInteger('audit_id')->nullable()->index()->comment('审核操作人ID');
            $table->timestamp('audit_at')->nullable()->comment('审核操作时间');
            $table->unsignedBigInteger('doc_app_id')->index()->comment('应用ID');
            $table->string('extra_nickname', 30)->nullable()->comment('用户在本文档中的备注昵称');
            $table->tinyInteger('role')->default(0)->index()->comment('在本文档中拥有的角色；0:待审核，3：参与者/伙伴：5：文档编辑；7：管理员，9：创始人');
            $table->tinyInteger('status')->default(0)->index()->comment('所属状态；0:待审核；1：同意，2：驳回，3：移出');
            $table->timestamps();
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `docs_app_users` comment '接口文档应用成员列表'");

        Schema::create('docs_app_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index()->comment('创建用户ID');
            $table->unsignedBigInteger('doc_app_id')->index()->comment('应用ID');
            $table->string('name', 60)->comment('菜单名称');
            $table->tinyInteger('open_type')->default(0)->index()->comment('所属状态；1：公开，2：登录可见，3：仅自己可见');
            $table->integer('sort')->default(0)->comment('排序；值越大越靠前');
            $table->string('group', 20)->default('guide')->comment('栏目分组；guide:指南，api:接口，faq:常见问题');
            $table->unsignedBigInteger('parent_id')->default(0)->nullable()->index()->comment('父级菜单id');
            $table->timestamps();
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `docs_app_menus` comment '文档应用菜单'");

        Schema::create('docs_docs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index()->comment('创建用户ID');
            $table->unsignedBigInteger('doc_app_id')->index()->comment('应用ID');
            $table->unsignedBigInteger('doc_menu_id')->index()->comment('应用菜单ID');
            $table->string('title')->comment('接口标题');
            $table->longText('content')->comment('文档内容或者api描述');
            $table->longText('content_html')->nullable()->comment('content由markdown格式转换为html后的内容[针对markdown]');
            $table->integer('sort')->default(0)->comment('排序；值越大越靠前');
            $table->tinyInteger('type')->default(2)->index()->comment('文档类型；1：富文本，2：Markdown，3：api 接口');
            $table->string('group', 20)->default('guide')->comment('栏目分组；guide:指南，api:接口，faq:常见问题');
            $table->tinyInteger('open_type')->default(0)->index()->comment('开放状态；1：公开，2：登录可见，3：仅创建用户自己可见,9:敏感待审核');
            $table->timestamps();
            $table->string('method', 10)->nullable()->default('get')->comment('接口请求类型');
            $table->string('api_url')->nullable()->default('')->comment('接口请求路径');
            $table->text('request_headers')->nullable()->comment('请求头信息');
            $table->text('request_body')->nullable()->comment('请求主体');
            $table->text('request_examples')->nullable()->comment('请求数据样例');
            $table->text('response_examples')->nullable()->comment('响应样例');

            $table->integer('view')->default(0)->comment('预览次数');
            $table->integer('spider')->default(0)->comment('爬虫访问预览次数');
            $table->integer('like')->default(0)->comment('点赞次数');

            if (\DB::getDriverName() !== 'sqlite') {
                $table->fulltext('title');
                $table->fulltext('content');
                $table->fulltext('content_html');
                $table->fulltext(['title', 'content']);
            }
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `docs_docs` comment '文档里面的文章'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('docs_apps');
        Schema::dropIfExists('docs_app_users');
        Schema::dropIfExists('docs_app_menus');
        Schema::dropIfExists('docs_docs');
    }
};
