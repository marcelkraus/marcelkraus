<?php

declare(strict_types=1);

namespace App\Service;

use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The availability line of the page and of the printed curriculum vitae,
 * rendered from `app.employment_end_date`. „Spätestens“, because leaving
 * earlier stays possible: the line states the outer bound, which is the only
 * part of it that is certain.
 */
final class AvailabilityStatement
{
    public function __construct(
        private readonly GermanDateFormatter $germanDateFormatter,
        #[Autowire('%app.employment_end_date%')]
        private readonly string $employmentEndDate,
    ) {
    }

    public function line(): string
    {
        $firstDayAfterEmployment = (new DateTimeImmutable($this->employmentEndDate))->modify('+1 day');

        return sprintf('Spätestens ab %s', $this->germanDateFormatter->month($firstDayAfterEmployment));
    }
}
