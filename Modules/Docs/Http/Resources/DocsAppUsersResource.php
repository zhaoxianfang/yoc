<?php

namespace Modules\Docs\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Docs\Models\DocsAppUser;

class DocsAppUsersResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'audit_id' => $this->audit_id,
            'audit_at' => $this->audit_at,
            'doc_app_id' => $this->doc_app_id,
            'extra_nickname' => $this->extra_nickname,
            'role' => $this->role,
            'role_text' => DocsAppUser::$rolesMaps[$this->role ?? DocsAppUser::ROLE_WAIT],
            'status' => $this->status,
            'status_text' => DocsAppUser::$statusMaps[$this->status ?? DocsAppUser::STATUS_WAIT],
        ];
    }
}
