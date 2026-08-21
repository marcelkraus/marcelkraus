<?php

declare(strict_types=1);

namespace App\Service;

use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Turns the notice period into the month Marcel can actually start, so the
 * statement on the page can never go stale. A hand-maintained "available
 * immediately" is wrong the day the contract situation moves; a computed month
 * answers the only question a recruiter has — when? — and answers it on every
 * request.
 *
 * Two weeks' notice to the end of a month means the notice has to arrive by
 * the last day of that month minus fourteen. That boundary is the 17th in a
 * 31-day month and the 16th in a 30-day one, which is why it is derived from
 * the month rather than written down as a fixed day: a fixed day would give
 * away a whole month twice a year.
 */
final class AvailabilityCalculator
{
    private const NOTICE_PERIOD_IN_DAYS = 14;

    public function __construct(
        private readonly GermanDateFormatter $dateFormatter,
        #[Autowire('%app.employment_end_date%')]
        private readonly string $employmentEndDate,
    ) {
    }

    /**
     * The label for the availability fact, e.g. "Ab Oktober 2026" or "Sofort".
     */
    public function availabilityLabel(DateTimeImmutable $today): string
    {
        $earliestStart = $this->earliestStart($today);

        if ($earliestStart === null) {
            return 'Sofort';
        }

        return sprintf('Ab %s', $this->dateFormatter->monthAndYear($earliestStart));
    }

    /**
     * The first day of the month Marcel can start, or null once the employment
     * has ended and nothing stands in the way any more.
     */
    public function earliestStart(DateTimeImmutable $today): ?DateTimeImmutable
    {
        $today = $today->setTime(0, 0);
        $freeFrom = (new DateTimeImmutable($this->employmentEndDate))->setTime(0, 0)->modify('+1 day');

        if ($today >= $freeFrom) {
            return null;
        }

        $endOfThisMonth = $today->modify('last day of this month');
        $latestNotice = $endOfThisMonth->modify(sprintf('-%d days', self::NOTICE_PERIOD_IN_DAYS));

        // Past the deadline the current month is out of reach, so the earliest
        // the contract can end is the end of the following month.
        $endOfEmployment = $today <= $latestNotice
            ? $endOfThisMonth
            : $today->modify('first day of next month')->modify('last day of this month');

        $earliestStart = $endOfEmployment->modify('+1 day');

        // Terminating early can only ever bring the date forward. Once the
        // notice reaches past the contract's own end, the contract wins.
        return $earliestStart > $freeFrom ? $freeFrom : $earliestStart;
    }
}
