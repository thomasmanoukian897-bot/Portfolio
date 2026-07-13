<?php

namespace App\Http\Requests;

use App\Enums\GroupAddPermission;
use App\Enums\MessagePermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfilePrivacyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'likes_public' => ['sometimes', 'boolean'],
            'bookmarks_public' => ['sometimes', 'boolean'],
            'group_add_permission' => ['sometimes', Rule::enum(GroupAddPermission::class)],
            'message_permission' => ['sometimes', Rule::enum(MessagePermission::class)],
        ];
    }

    /**
     * @return array<string, bool|GroupAddPermission|MessagePermission>
     */
    public function privacySettings(): array
    {
        return [
            'likes_public' => $this->boolean('likes_public'),
            'bookmarks_public' => $this->boolean('bookmarks_public'),
            'group_add_permission' => $this->enum('group_add_permission', GroupAddPermission::class)
                ?? $this->user()->group_add_permission,
            'message_permission' => $this->enum('message_permission', MessagePermission::class)
                ?? $this->user()->message_permission,
        ];
    }
}
