<?php

namespace Modules\Home\Http\Controllers\Web\Tools\Generate;

use Illuminate\Http\Request;
use Modules\Home\Http\Controllers\HomeBaseController;
use zxf\Encryption\RSA;
use zxf\Tools\IDCardGenerator;

/**
 * 身份证 生成和验证器
 */
class RsaEncryption extends HomeBaseController
{
    /**
     * rsa 加密
     */
    public function index(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('home::tools.generate.rea', []);
        }
        $handle_type = $request->input('handle_type');
        $digestAlg = $request->input('digest_alg', 'sha512'); // 摘要算法，默认为sha512
        $keyLength = $request->input('key_length', '2048'); // 密钥长度，默认为2048位；指定应该使用多少位来生成私钥
        $beforeString = $request->input('before_string', ''); // 加密前的字符串
        $afterString = $request->input('after_string', ''); // 加密后的字符串
        $padding = $request->input('padding', 'OPENSSL_PKCS1_PADDING'); // 填充参数,默认为OPENSSL_PKCS1_PADDING
        $publicKey = $request->input('public_key', ''); // 公钥文件路径或PEM格式的字符串
        $privateKey = $request->input('private_key', ''); // 私钥文件路径或PEM格式的字符串
        $padding = constant($padding);
        switch ($handle_type) {
            case 'generate_key':
                $res = RSA::generateKeyPair($keyLength, false, '', '', $digestAlg);
                if (! $res) {
                    return $this->error([
                        'code' => 500,
                        'message' => '密钥对生成失败',
                    ]);
                } else {
                    $res['fn'] = 'generate_key';
                    $res['message'] = '密钥对生成成功';

                    return $this->success($res);
                }
                break;
            case 'encryption':
                if (empty($beforeString)) {
                    return $this->error([
                        'code' => 412,
                        'message' => '加密内容不能为空',
                    ]);
                }
                // 使用公钥加密数据
                $outputFormat = 'base64'; // 输出格式（'base64'或'hex'）
                $encrypted = RSA::encryptWithPublicKey($beforeString, $outputFormat, $publicKey, $padding);
                if ($encrypted === null) {
                    return $this->error([
                        'code' => 500,
                        'message' => '加密失败',
                    ]);
                }

                return $this->success([
                    'result' => $encrypted,
                    'fn' => 'encryption',
                    'message' => '加密成功',
                ]);
                break;
            case 'decrypt':
                if (empty($afterString)) {
                    return $this->error([
                        'code' => 412,
                        'message' => '解密内容不能为空',
                    ]);
                }
                // 使用私钥解密数据（通常在另一个安全的环境中执行）
                $inputFormat = 'base64'; // 输入格式（'base64'或'hex'）
                $decrypted = RSA::decryptWithPrivateKey($afterString, $inputFormat, $privateKey, $padding); // 注意：解密操作应该在可以安全访问私钥的环境中进行
                if ($decrypted === null) {
                    return $this->error([
                        'code' => 500,
                        'message' => '解密失败',
                    ]);
                }

                return $this->success([
                    'result' => $decrypted,
                    'fn' => 'decrypt',
                    'message' => '解密成功',
                ]);
                break;
            default:
                return $this->error([
                    'code' => 412,
                    'message' => '不识别的操作',
                ]);
                break;
        }

    }

    /**
     * aes 解密
     */
    public function aes(Request $request)
    {
        return view('home::tools.generate.rea', []);
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
