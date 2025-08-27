<?php

namespace Modules\Demo\Http\Controllers\Web\Table;

use Illuminate\Http\Request;
use Modules\Demo\Http\Controllers\DemoBaseController;

// Datatables 示例
class DataTables extends DemoBaseController
{
    public function index(Request $request)
    {
        return view('demo::tables.index');
    }

    public function data()
    {
        $req = request()->input();
        $data = [
            [
                'id' => 1,
                'user' => [
                    'cover' => 'https://www.baidu.com/img/bd_logo1.png',
                ],
                // badge 显示标签，badge-*可用类型：eg:(badge badge-outline-success)
                //      default,
                //      outline-(dark,light,purple,danger,warning,info,success,secondary,primary)
                //      soft-(dark,light,purple,danger,warning,info,success,secondary,primary)
                'label' => 'soft-danger',
                // ti 的icon图标
                'icon' => 'wifi',
                'input' => 'https://weifang.com',
                'ip' => '192.168.1.1',
                'nickname' => 'admin',
                'mobile' => '18345678901',
                'mobile_verified_at' => '2019-09-03 09:09:09',
                'email' => '123@example.com',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36',
                'created_at' => '2019-09-03 09:09:09',
                'updated_at' => '2019-09-03 09:09:09',
                'sub_tasks' => 1,
                'gender' => 1,
                'status' => 1,
            ],
            [
                'id' => 2,
                'user' => [
                    'cover' => 'https://www.baidu.com/img/bd_logo1.png',
                ],
                'label' => 'outline-success',
                'icon' => 'ambulance',
                'input' => 'https://weifang.com/docs',
                'ip' => '192.168.1.2',
                'nickname' => 'admin',
                'mobile' => '18345678901',
                'mobile_verified_at' => '2019-09-03 09:09:09',
                'email' => '123@example.com',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36',
                'created_at' => '2019-09-03 09:09:09',
                'updated_at' => '2019-09-03 09:09:09',
                'sub_tasks' => 0,
                'gender' => 1,
                'status' => 1,
            ],
            [
                'id' => 3,
                'user' => [
                    'cover' => 'https://www.baidu.com/img/bd_logo1.png',
                ],
                'label' => 'outline-primary',
                'icon' => 'analyze',
                'input' => 'https://weifang.com/docs/2',
                'ip' => '192.168.1.3',
                'nickname' => 'admin',
                'mobile' => '18345678901',
                'mobile_verified_at' => '2019-09-03 09:09:09',
                'email' => '123@example.com',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36',
                'created_at' => '2019-09-03 09:09:09',
                'updated_at' => '2019-09-03 09:09:09',
                'sub_tasks' => 1,
                'gender' => 1,
                'status' => 1,
            ],
        ];

        return $this->dataTables($data, 100);
    }
}
