<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\AvailabilityCalculator;
use App\Service\GermanDateFormatter;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Pure logic, so a plain TestCase: no kernel, no request. The cases that
 * matter are the boundaries, because they move with the length of the month –
 * the 17th in a 31-day month, the 16th in a 30-day one. A fixed boundary would
 * pass a naive test and give away a whole month twice a year.
 */
final class AvailabilityCalculatorTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function availabilityCases(): iterable
    {
        yield 'notice still fits into a 31-day August' => ['2026-08-17', 'Ab September 2026'];
        yield 'one day past the August deadline' => ['2026-08-18', 'Ab Oktober 2026'];
        yield 'notice still fits into a 30-day September' => ['2026-09-16', 'Ab Oktober 2026'];
        yield 'one day past the September deadline' => ['2026-09-17', 'Ab November 2026'];
        yield 'notice still fits into a 31-day October' => ['2026-10-17', 'Ab November 2026'];
        yield 'one day past the October deadline' => ['2026-10-18', 'Ab Dezember 2026'];
        yield 'notice still fits into a 30-day November' => ['2026-11-16', 'Ab Dezember 2026'];
        yield 'past the last deadline, capped by the contract itself' => ['2026-11-17', 'Ab Dezember 2026'];
        yield 'the day the employment ends' => ['2026-11-30', 'Ab Dezember 2026'];
        yield 'the first day after the employment' => ['2026-12-01', 'Sofort'];
        yield 'well after the employment' => ['2027-03-04', 'Sofort'];
    }

    #[DataProvider('availabilityCases')]
    public function testAvailabilityLabel(string $today, string $expected): void
    {
        $calculator = new AvailabilityCalculator(new GermanDateFormatter(), '2026-11-30');

        self::assertSame($expected, $calculator->availabilityLabel(new DateTimeImmutable($today)));
    }

    /**
     * Terminating early can only ever bring the start forward, never push it
     * past the day the contract ends on its own.
     */
    public function testTheStartIsNeverLaterThanTheEndOfTheEmployment(): void
    {
        $calculator = new AvailabilityCalculator(new GermanDateFormatter(), '2026-11-30');

        self::assertEquals(
            new DateTimeImmutable('2026-12-01 00:00:00'),
            $calculator->earliestStart(new DateTimeImmutable('2026-11-17')),
        );
    }

    public function testNoStartDateIsReturnedOnceTheEmploymentHasEnded(): void
    {
        $calculator = new AvailabilityCalculator(new GermanDateFormatter(), '2026-11-30');

        self::assertNull($calculator->earliestStart(new DateTimeImmutable('2026-12-01')));
    }
}
