<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class MentionParser
{
    private const HANDLE_PATTERN = '/(?<![a-zA-Z0-9])@([a-z0-9]+(?:-[a-z0-9]+)*)/';

    /**
     * @return list<string>
     */
    public function extractHandles(string $body): array
    {
        preg_match_all(self::HANDLE_PATTERN, $body, $matches);

        return array_values(array_unique($matches[1]));
    }

    /**
     * @param  Collection<int, User>|iterable<int, User>  $users
     */
    public function render(string $body, iterable $users): string
    {
        /** @var Collection<string, User> $usersByHandle */
        $usersByHandle = collect($users)->keyBy('handle');

        $escapedBody = e($body);

        return preg_replace_callback(
            self::HANDLE_PATTERN,
            function (array $matches) use ($usersByHandle): string {
                $handle = $matches[1];
                $user = $usersByHandle->get($handle);

                if ($user === null) {
                    return $matches[0];
                }

                $url = e(route('users.show', $user));
                $label = e('@'.$handle);

                return '<a href="'.$url.'" class="font-semibold text-primary hover:text-blue-700 transition-colors">'.$label.'</a>';
            },
            $escapedBody,
        ) ?? $escapedBody;
    }
}
