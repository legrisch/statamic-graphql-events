<?php

namespace Legrisch\StatamicGraphlEvents\RRule;

use Carbon\Carbon;

class Occurrence {
    public Carbon $start;
    public Carbon | null $end;
    public bool $allDay;

    public function __construct(
        Carbon $start,
        Carbon | null $end,
        bool $allDay,
    )
    {
        $this->start = $start;
        $this->end = $end;
        $this->allDay = $allDay;
    }

    public static function sort(Occurrence $a, Occurrence $b): int {
        if ($a->start === $b->start) return 0;
        return $a->start < $b->start ? -1 : 1;
    }

    /**
     * @param Occurrence[] $occurrences
     * @param int $limit
     * @return Occurrence[]
     */
    public static function limit(array $occurrences, int $limit): array
    {
        return array_slice($occurrences, 0, $limit);
    }
}
