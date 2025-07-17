<?php

namespace Modules\Docs\Services;

use Modules\Docs\Models\DocsApp;

class DocsAppService
{
    public function getHomeDocs()
    {
        return DocsApp::query()
            ->open()
            ->with(['users'])
            ->withCount(['docs'])
            ->orderByDesc('sort')
            ->orderByDesc('created_at')
            ->paginate(12);
    }

    public function getMyDocs()
    {
        return DocsApp::query()
            ->mine()
            ->with(['users'])
            ->withCount(['docs'])
            ->orderByDesc('sort')
            ->orderByDesc('created_at')
            ->paginate(12);
    }
}
