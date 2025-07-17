<?php

namespace Modules\Docs\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Docs\Models\DocsAppUser;
use Modules\Users\Models\User;

class DocsUsersResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'real_name' => $this->real_name,
            'nickname' => $this->nickname,
            'gender' => $this->gender,
            'cover' => $this->cover,
            'email' => $this->email,
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDateTimeString(),
            'status' => $this->status,
            'status_text' => User::$statusMaps[$this->status ?? User::STATUS_NOT_USED],

            'user_role' => $this->pivot->role,
            'user_role_text' => DocsAppUser::$rolesMaps[$this->pivot->role ?? DocsAppUser::ROLE_WAIT],
            'user_status' => $this->pivot->status,
            'user_status_text' => DocsAppUser::$statusMaps[$this->pivot->status ?? DocsAppUser::STATUS_WAIT],
            'extra_nickname' => $this->pivot->extra_nickname ?? '',
        ];
    }
}
