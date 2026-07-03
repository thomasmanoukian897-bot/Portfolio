<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $author = User::query()->where('email', 'admin@example.com')->first()
            ?? User::factory()->admin()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);

        $categories = Category::query()->pluck('id', 'slug');

        $posts = [
            [
                'title' => 'Why We Choose Laravel for Client Projects',
                'slug' => 'why-we-choose-laravel-for-client-projects',
                'excerpt' => 'Laravel gives us the speed, structure, and ecosystem we need to ship reliable products without cutting corners.',
                'content' => <<<'HTML'
<p>When we evaluate a stack for a new client project, we look for three things: developer productivity, long-term maintainability, and a mature ecosystem. Laravel consistently checks all three boxes.</p>
<p>Its conventions reduce decision fatigue, Eloquent makes data modeling approachable, and the surrounding tooling — queues, scheduling, authentication — means we spend less time reinventing infrastructure and more time solving business problems.</p>
<p>For teams that need to move fast without accumulating technical debt, Laravel remains one of the most pragmatic choices available.</p>
HTML,
                'published_at' => now()->subDays(12),
                'category_slugs' => ['development', 'business'],
            ],
            [
                'title' => 'Design Systems That Scale With Your Product',
                'slug' => 'design-systems-that-scale-with-your-product',
                'excerpt' => 'A thoughtful design system keeps your UI consistent as features multiply and your team grows.',
                'content' => <<<'HTML'
<p>Early-stage products often ship with ad-hoc UI decisions. That works until you add a second developer, a third feature area, or a marketing landing page that needs to match the app.</p>
<p>A design system does not have to be a massive Figma library on day one. Start with typography, color tokens, spacing rules, and a handful of reusable components. Document when to use each pattern.</p>
<p>The payoff is consistency without slowing delivery — every new screen inherits the same visual language automatically.</p>
HTML,
                'published_at' => now()->subDays(8),
                'category_slugs' => ['design', 'development'],
            ],
            [
                'title' => 'From MVP to Launch: A Practical Roadmap',
                'slug' => 'from-mvp-to-launch-a-practical-roadmap',
                'excerpt' => 'Shipping an MVP is only the beginning. Here is how we guide products from first release to a confident public launch.',
                'content' => <<<'HTML'
<p>Most founders underestimate what happens after the MVP ships. Real users surface edge cases, performance bottlenecks appear under load, and onboarding friction becomes impossible to ignore.</p>
<p>We break the post-MVP phase into focused milestones: stabilize core flows, instrument analytics, tighten performance, and polish the first-run experience. Each milestone has a clear definition of done.</p>
<p>Launch day should feel like a checkpoint, not a cliff. The best releases are the ones where the team already knows how the product behaves in production.</p>
HTML,
                'published_at' => now()->subDays(5),
                'category_slugs' => ['business', 'development'],
            ],
            [
                'title' => 'Building Accessible Interfaces from Day One',
                'slug' => 'building-accessible-interfaces-from-day-one',
                'excerpt' => 'Accessibility is not a polish pass at the end — it is a design constraint that leads to better products for everyone.',
                'content' => <<<'HTML'
<p>Keyboard navigation, semantic HTML, sufficient color contrast, and meaningful labels are not optional extras. They are baseline requirements for professional software.</p>
<p>We bake accessibility into component specs from the start: focus states, ARIA attributes where needed, and tested screen-reader flows for critical paths like checkout or account creation.</p>
<p>The result is a product that works for more people and is easier to maintain, because the markup and interactions were intentional from the beginning.</p>
HTML,
                'published_at' => now()->subDays(2),
                'category_slugs' => ['design', 'development'],
            ],
            [
                'title' => 'What Makes a Great Portfolio Website',
                'slug' => 'what-makes-a-great-portfolio-website',
                'excerpt' => 'Your portfolio should communicate expertise quickly. These are the patterns we see work best for creative and technical teams.',
                'content' => <<<'HTML'
<p>A portfolio is not a résumé with animations. It is a sales tool. Visitors should understand what you do, who you help, and why they should trust you within the first ten seconds.</p>
<p>Lead with outcomes, not tools. Show real project context, the problem you solved, and the result. Keep navigation simple and make contact effortless.</p>
<p>The best portfolio sites feel focused. Every section earns its place by answering a question the client already has.</p>
HTML,
                'published_at' => now()->subDay(),
                'category_slugs' => ['design', 'business'],
            ],
            [
                'title' => 'Upcoming: Refactoring Legacy Codebases',
                'slug' => 'upcoming-refactoring-legacy-codebases',
                'excerpt' => 'A draft post on our approach to modernizing legacy applications without stopping feature delivery.',
                'content' => <<<'HTML'
<p>Legacy does not mean broken. It means the codebase has history — and history is valuable context.</p>
<p>This article will cover how we map critical paths, introduce tests around high-risk areas, and migrate incrementally rather than betting everything on a big-bang rewrite.</p>
HTML,
                'published_at' => null,
                'category_slugs' => ['development', 'business'],
            ],
        ];

        foreach ($posts as $postData) {
            $categorySlugs = $postData['category_slugs'];
            unset($postData['category_slugs']);

            $post = Post::query()->updateOrCreate(
                ['slug' => $postData['slug']],
                [
                    ...$postData,
                    'user_id' => $author->id,
                ],
            );

            $categoryIds = collect($categorySlugs)
                ->map(fn (string $slug) => $categories->get($slug))
                ->filter()
                ->values()
                ->all();

            $post->categories()->sync($categoryIds);
        }

        if ($categories->isNotEmpty()) {
            Post::factory()
                ->count(3)
                ->for($author)
                ->published()
                ->create()
                ->each(function (Post $post) use ($categories): void {
                    $post->categories()->attach(
                        $categories->values()->random(min($categories->count(), rand(1, 2)))->all()
                    );
                });
        }
    }
}
