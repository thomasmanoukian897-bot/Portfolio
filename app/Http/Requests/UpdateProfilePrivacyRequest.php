<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePrivacyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'likes_public' => ['sometimes', 'boolean'],
            'bookmarks_public' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, bool>
     */
    public function privacySettings(): array
    {
        return [
            'likes_public' => $this->boolean('likes_public'),
            'bookmarks_public' => $this->boolean('bookmarks_public'),
        ];
    }
}
