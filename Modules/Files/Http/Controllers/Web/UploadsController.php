<?php

namespace Modules\Files\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Files\Http\Controllers\Web\RichText\Cherry;
use Modules\Files\Http\Controllers\Web\RichText\CkEditor;
use Modules\Files\Http\Controllers\Web\RichText\EditorMd;
use Modules\Files\Http\Controllers\Web\RichText\Summernote;

class UploadsController extends Controller
{
    protected $richTextMap = [
        'editor' => EditorMd::class,
        'ckeditor' => CkEditor::class,
        'cherry' => Cherry::class,
        'summernote' => Summernote::class,
    ];

    /**
     * 上传文件入口
     *
     * @param  string  $richText  富文本类型：editor、ckeditor、cherry
     * @param  string  $fileType  文件类型：img、file、video
     * @param  string  $driver  文件驱动：docs、images、photos,files
     * @return mixed
     */
    public function store(string $richText, string $fileType, Request $request, string $driver = 'file')
    {
        try {
            return $this->richTextMap[$richText]::{$fileType}($request, $driver);
        } catch (\Exception $exception) {
            return response()->json([
                'code' => 500,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
