<?php

namespace Modules\Callback\Http\Controllers\Web\Test;

use Modules\Callback\Http\Controllers\Web\CallbackController;

class Test extends CallbackController
{
    public function __construct() {}

    /**
     * 测试回调
     */
    public function index()
    {
        if (empty($_POST)) {
            $content = file_get_contents('php://input');
            $post = (array) json_decode($content, true);
        } else {
            $post = $_POST;
        }
        $data = array_merge(request()->all(), $post);
        debug_test($data, '测试回调');

        return 'Test Success!';

    }
}
