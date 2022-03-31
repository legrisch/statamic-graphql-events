<?php

namespace Legrisch\StatamicGraphQLEvents\RRule;

use Carbon\Carbon;
use Statamic\Fields\Value;

class EventDates
{
  /** @var EventDate[] $dates */
  public array $eventDates = [];

  /**
   * @param mixed $data Augmented Array of event entry
   */
  public function __construct(mixed $data)
  {
    /** @var Value $dates */
    $dates = $data['dates'];

    foreach ($dates->value() as $dateData) {
      $eventDate = new EventDate($dateData);
      array_push($this->eventDates, $eventDate);
    }
  }

  /**
   * @param int|null $limit
   * @return array
   */
  public function occurrences(int $limit = null): array
  {
    /** @var Occurrence[] $occurrences */
    $occurrences = [];

    foreach ($this->eventDates as $eventDate) {
      $eventDateOccurrences = $eventDate->occurrences();
      $occurrences = array_merge($occurrences, $eventDateOccurrences);
    }

    usort($occurrences, [Occurrence::class, 'sort']);
    if ($limit) $occurrences = Occurrence::limit($occurrences, $limit);
    return $occurrences;
  }

  public function occurrencesAfter(Carbon|string $after, int $limit = null): array
  {
    /** @var Occurrence[] $occurrences */
    $occurrences = [];

    foreach ($this->eventDates as $eventDate) {
      $eventDateOccurrences = $eventDate->occurrencesAfter($after);
      $occurrences = array_merge($occurrences, $eventDateOccurrences);
    }

    usort($occurrences, [Occurrence::class, 'sort']);
    if ($limit) $occurrences = Occurrence::limit($occurrences, $limit);
    return $occurrences;
  }

  /**
   * @param Carbon|string $from
   * @param Carbon|string $to
   * @return Occurrence[]
   */
  public function occurrencesBetween(Carbon|string $from, Carbon|string $to): array
  {
    /** @var Occurrence[] $occurrences */
    $occurrences = [];

    foreach ($this->eventDates as $eventDate) {
      $eventDateOccurrences = $eventDate->occurrencesBetween($from, $to);
      $occurrences = array_merge($occurrences, $eventDateOccurrences);
    }

    usort($occurrences, [Occurrence::class, 'sort']);
    return $occurrences;
  }

  /**
   * @param Carbon|string $from
   * @param Carbon|string $to
   * @return bool
   */
  public function hasOccurrencesBetween(Carbon|string $from, Carbon|string $to): bool
  {
    return count($this->occurrencesBetween($from, $to)) > 0;
  }

  /**
   * @param Carbon|string $after
   * @return bool
   */
  public function hasOccurrencesAfter(Carbon|string $after): bool
  {
    return count($this->occurrencesAfter($after, 1)) > 0;
  }
}
