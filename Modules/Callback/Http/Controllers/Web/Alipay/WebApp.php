<?php

namespace Modules\Callback\Http\Controllers\Web\Alipay;

use Illuminate\Http\Request;
use Modules\Callback\Http\Controllers\Web\CallbackController;
use Modules\Test\Models\Test;

class WebApp extends CallbackController
{
    public function index(Request $request)
    {
        Test::create([
            'title' => 'alipay web app callback',
            'content' => json_encode($request->header()),
            'object' => $request->input(),
        ]);
    }
}
