<?php

namespace Modules\Home\Http\Controllers\Web\Tools\Generate;

use Illuminate\Http\Request;
use Modules\Home\Http\Controllers\HomeBaseController;
use zxf\Tools\IDCardGenerator;

/**
 * 身份证 生成和验证器
 */
class IDCard extends HomeBaseController
{
    public function index(Request $request)
    {
        $provinces = IDCardGenerator::getProvinces();

        return view('home::tools.generate.id_card', [
            'provinces' => $provinces, // 支持的省份
        ]);
    }

    /**
     * 身份证生成
     */
    public function generate(Request $request)
    {
        $type = $request->input('type', 'generate'); // 表单处理类型
        if ($type != 'generate') {
            return $this->validate($request);
        }

        $multiple = $request->input('multiple', 1); // 生成的数量
        $province = $request->input('province', null);
        $gender = $request->input('gender', null);
        $birthday = $request->input('birthday', null);
        $options = [
            'province' => $province,
            'gender' => $gender,
            'birthday' => $birthday,
        ];

        // 生成18位身份证
        $idCards = IDCardGenerator::generateBatch($multiple, $options);

        return $this->success([
            'list' => $idCards,
        ]);

    }

    /**
     * 身份证验证 解析
     */
    public function validate(Request $request)
    {
        $type = $request->input('type', 'generate'); // 表单处理类型
        if ($type != 'validate') {
            return $this->generate($request);
        }
        $idCard = $request->input('id_card', null);

        $result = IDCardGenerator::parse($idCard);
        return $this->success([
            'list' => $result,
        ]);
    }
}
