<?php

namespace Database\Factories;

use App\Enums\ConversationType;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => ConversationType::Direct,
            'name' => null,
            'created_by' => User::factory(),
        ];
    }

    public function direct(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => ConversationType::Direct,
            'name' => null,
        ]);
    }

    public function group(?string $name = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => ConversationType::Group,
            'name' => $name ?? fake()->words(2, true),
        ]);
    }
}
