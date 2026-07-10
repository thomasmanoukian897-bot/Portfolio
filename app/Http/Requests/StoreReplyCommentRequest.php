<?php

namespace App\Http\Requests;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreReplyCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Post $post */
        $post = $this->route('post');

        /** @var Comment $comment */
        $comment = $this->route('comment');

        $user = $this->user();

        return $user !== null
            && (int) $comment->post_id === (int) $post->id
            && ! $comment->isReply()
            && $user->can('create', [Comment::class, $post]);
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

    protected function failedValidation(Validator $validator): void
    {
        /** @var Comment $comment */
        $comment = $this->route('comment');

        session()->flash('reply_to', $comment->id);

        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
}
