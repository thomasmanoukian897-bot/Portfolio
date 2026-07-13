<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        Category::query()->insert([
            ['name' => 'Development', 'slug' => 'development', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Design', 'slug' => 'design', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Business', 'slug' => 'business', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Video', 'slug' => 'video', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->call(PostSeeder::class);
    }
}
