<?php

namespace Modules\App\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Modules\App\Models\AppVersion;

class AppController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function download()
    {
        $androidInfo = AppVersion::query()
            ->where('platform', 'android')
            ->where('status', 1)
            ->orderByDesc('version_num')
            ->first();

        return view('app::download', [
            'androidInfo' => $androidInfo,
        ]);
    }

    /**
     * android App 下载
     */
    public function android()
    {
        $info = AppVersion::query()
            ->where('platform', 'android')
            ->where('status', 1)
            ->orderByDesc('version_num')
            ->first();

        return redirect($info->url);
    }
}
