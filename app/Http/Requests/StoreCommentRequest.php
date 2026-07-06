<?php

namespace App\Http\Requests;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Post $post */
        $post = $this->route('post');

        return $this->user()?->can('create', [Comment::class, $post]) ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
