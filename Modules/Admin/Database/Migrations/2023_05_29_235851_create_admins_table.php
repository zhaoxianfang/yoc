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
        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->unique()->index()->comment('关联用户ID');
            // $table->unsignedBigInteger('admin_group_id')->nullable()->index()->comment('关联管理员角色组ID');
            $table->string('nickname', 30)->nullable()->comment('昵称');
            $table->string('mobile', 15)->unique()->nullable();
            $table->timestamp('mobile_verified_at')->nullable()->comment('手机号认证时间');
            $table->string('email', 40)->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable()->comment('邮箱认证时间');
            $table->string('id_card', 20)->nullable()->unique()->comment('身份证号');
            $table->string('password')->nullable();
            $table->unsignedTinyInteger('gender')->default(0)->comment('性别：0未设置，1男，2女');
            $table->string('cover')->nullable()->index()->comment('头像');
            $table->string('source', 50)->nullable()->index()->comment('用户来源');
            $table->string('open_id')->unique()->nullable()->comment('标识（第三方应用的唯一标识）');
            $table->string('unionid')->unique()->nullable()->index();
            $table->unsignedBigInteger('create_by')->default(0)->index()->comment('邀约人');
            $table->rememberToken();
            $table->timestamps();
            $table->string('remark')->nullable()->comment('备注');
            $table->unsignedTinyInteger('status')->default(1)->index()->comment('状态：0未激活，1正常，2冻结');
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `admins` comment '管理员列表'");

        Schema::create('admin_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('pid')->default(0)->index()->comment('父级id');
            $table->string('identify', 60)->unique()->index()->nullable()->comment('菜单权限唯一标识，例如：edit_system_config');
            $table->string('name')->unique()->index()->nullable()->comment('路由地址 例如： admin/test 或者 admin.test');
            $table->string('title', 60)->nullable()->comment('菜单名称 例如：控制面板');
            $table->unsignedTinyInteger('ismenu')->default(1)->index()->comment('是否为菜单：1菜单，0按钮');
            $table->integer('weigh')->default(0)->comment('权重');
            $table->string('icon', 40)->nullable()->comment('小图标');
            $table->string('badge_text', 20)->nullable()->comment('徽标');
            $table->string('badge_text_style', 20)->nullable()->default('label-info')->comment('徽标样式');
            $table->unsignedBigInteger('create_by')->default(0)->index()->comment('创建人');
            $table->string('remarks')->nullable()->comment('备注');
            $table->timestamps();
            $table->tinyInteger('status')->default(1)->index()->comment('应用状态；1正常，0停用');
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `admin_menus` comment '后台菜单'");

        Schema::create('admin_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('group_name', 60)->nullable()->comment('角色组/机构 名称');
            $table->integer('pid')->default(0)->index()->comment('父级组id,pid 为0 时候 配合expiration_at 不为空 验证此角色组可用时间');
            $table->dateTime('expiration_at')->nullable()->comment('如果是为机构创建的角色组，可以设置使用的过期时间');
            $table->unsignedBigInteger('create_by')->default(0)->index()->comment('创建人');
            $table->timestamps();
            $table->tinyInteger('status')->default(1)->index()->comment('状态；1正常，0停用');
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `admin_groups` comment '管理员角色组'");

        Schema::create('admin_group_map', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->default(0)->index()->comment('管理员ID');
            $table->unsignedBigInteger('group_id')->default(0)->index()->comment('角色组ID');
            $table->timestamps();
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `admin_group_map` comment '管理员-角色组 关联表'");

        Schema::create('admin_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->default(0)->index()->comment('管理员角色组ID');
            $table->unsignedBigInteger('menu_id')->default(0)->index()->comment('菜单ID');
        });
        \DB::getDriverName() !== 'sqlite' && \DB::statement("ALTER TABLE `admin_roles` comment '管理员权限'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins');
        Schema::dropIfExists('admin_menus');
        Schema::dropIfExists('admin_groups');
        Schema::dropIfExists('admin_group_map');
        Schema::dropIfExists('admin_roles');
    }
};
