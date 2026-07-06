<?php

namespace Database\Factories;

use App\Enums\CommentVoteType;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommentVote>
 */
class CommentVoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comment_id' => Comment::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(CommentVoteType::cases()),
        ];
    }

    public function upvote(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CommentVoteType::Up,
        ]);
    }

    public function downvote(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CommentVoteType::Down,
        ]);
    }
}
