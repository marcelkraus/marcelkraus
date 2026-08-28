<?php

declare(strict_types=1);

namespace App\Content;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Reads the JSON content files. Extracted from the controller once the printed
 * curriculum vitae needed the same data: two readers on the same four files
 * would be two chances for the page and the document to drift apart, and the
 * whole point of generating the document is that they cannot.
 *
 * A missing or malformed file degrades to an empty list rather than an error –
 * the failure that goes unnoticed is the one worth designing for.
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
