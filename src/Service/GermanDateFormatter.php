<?php

declare(strict_types=1);

namespace App\Service;

use DateTimeImmutable;

/**
 * German month names for the dateline of the printed curriculum vitae. The
 * project deliberately carries no translation component and no intl dependency.
 */
final class GermanDateFormatter
{
    /**
     * Calendar order, not alphabetical: the index is the month number.
     *
     * @var array<int, string>
     */
    private const MONTH_NAMES = [
        1 => 'Januar',
        2 => 'Februar',
        3 => 'März',
        4 => 'April',
        5 => 'Mai',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'August',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Dezember',
    ];

    public function dayMonthAndYear(DateTimeImmutable $date): string
    {
        return sprintf(
            '%d. %s %s',
            (int) $date->format('j'),
            self::MONTH_NAMES[(int) $date->format('n')],
            $date->format('Y'),
        );
    }
}
