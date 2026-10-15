<?php

declare(strict_types=1);

namespace App\Content;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The one reader of the content files, for the page and the printed document
 * alike. A missing or malformed file reads as an empty list.
 */
final class ContentRepository
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function load(string $name): array
    {
        $path = $this->projectDir . '/config/content/' . $name . '.json';
        if (is_file($path) === false) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
