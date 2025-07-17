<?php

namespace Modules\Callback\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CallbackController extends Controller
{
    public function tips(Request $request)
    {
        return view('errors.tips', [
            'message' => '无回调.',
        ]);
    }
}
