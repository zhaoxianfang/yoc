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
                'nickname' => 'admin',
                'mobile' => '18345678901',
                'mobile_verified_at' => '2019-09-03 09:09:09',
                'email' => '123@example.com',
                'id_card' => '123456789',
                'created_at' => '2019-09-03 09:09:09',
                'updated_at' => '2019-09-03 09:09:09',
                'gender' => 1,
                'status' => 1,
            ],
            [
                'id' => 2,
                'user' => [
                    'cover' => 'https://www.baidu.com/img/bd_logo1.png',
                ],
                'nickname' => 'admin',
                'mobile' => '18345678901',
                'mobile_verified_at' => '2019-09-03 09:09:09',
                'email' => '123@example.com',
                'id_card' => '123456789',
                'created_at' => '2019-09-03 09:09:09',
                'updated_at' => '2019-09-03 09:09:09',
                'gender' => 1,
                'status' => 1,
            ],
            [
                'id' => 3,
                'user' => [
                    'cover' => 'https://www.baidu.com/img/bd_logo1.png',
                ],
                'nickname' => 'admin',
                'mobile' => '18345678901',
                'mobile_verified_at' => '2019-09-03 09:09:09',
                'email' => '123@example.com',
                'id_card' => '123456789',
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
