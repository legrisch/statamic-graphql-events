<?php

namespace Legrisch\StatamicGraphQLEvents\RRule;

use Carbon\Carbon;
use DateTimeInterface;
use RRule\RRule;

class EventDate
{
  public Carbon $dateStart;
  public bool $allDay;
  public string|null $dateTimeStart;
  public string|null $dateTimeEnd;
  public string $recurrenceFrequency;
  public int $recurrenceCustomInterval;
  public string $recurrenceCustomPeriod;
  public Carbon|null $recurrenceUntil;

  public function __construct($eventData)
  {
    $this->dateStart = $eventData->date_start;
    $this->allDay = $eventData->all_day;
    $this->dateTimeStart = $eventData->date_time_start;
    $this->dateTimeEnd = $eventData->date_time_end;
    $this->recurrenceFrequency = $eventData->recurrence_frequency;
    $this->recurrenceCustomInterval = $eventData->recurrence_custom_interval;
    $this->recurrenceCustomPeriod = $eventData->recurrence_custom_period;

    /* Repeat the event including the "until" date */
    $recurrenceUntilValue = $eventData->recurrence_until;
    $this->recurrenceUntil = $recurrenceUntilValue instanceof Carbon ? $recurrenceUntilValue->endOfDay() : null;
  }

  public function isValid(): bool
  {
    /* Event is not "all day" but no start date is given */
    if (!$this->allDay && !$this->dateTimeStart) return false;

    /* Event is set to repeat but not for how long */
    if ($this->recurrenceFrequency !== "none" && !$this->recurrenceUntil) return false;

    return true;
  }

  public function dtStart(): Carbon
  {
    return $this->isAllDay()
      ? $this->dateStart->copy()->startOfDay()
      : $this->dateStart->copy()->setTimeFromTimeString($this->dateTimeStart);
  }

  public function isRecurring(): bool
  {
    return $this->recurrenceFrequency !== "none";
  }

  public function isAllDay(): bool
  {
    return $this->allDay;
  }

  public function rRule(): RRule|null
  {
    if (!$this->isRecurring()) return null;
    return new RRule([
      "DTSTART" => $this->dtStart()->toDateTime(),
      "FREQ" => $this->recurrenceFrequency !== 'custom'
        ? strtoupper($this->recurrenceFrequency)
        : strtoupper($this->recurrenceCustomPeriod),
      "INTERVAL" => $this->recurrenceFrequency === 'custom'
        ? $this->recurrenceCustomInterval
        : 1,
      "UNTIL" => $this->recurrenceUntil->endOfDay()->toDateTime()
    ]);
  }

  public function rDate(): Carbon|null
  {
    if ($this->isRecurring()) return null;
    return $this->dtStart();
  }

  /**
   * @param int|null $limit
   * @return Occurrence[]
   */
  public function occurrences(int $limit = null): array
  {
    /** @var Occurrence[] $occurrences */
    $occurrences = [];

    /* Skip if invalid */
    if (!$this->isValid()) return $occurrences;

    $to = $this->recurrenceUntil;

    /* This should not happen actually as recurring events must have an "until" value */
    if ($this->isRecurring() && !$to) return $occurrences;

    if ($this->isRecurring()) {
      $rRuleOccurrences = $this->rRule()->getOccurrences($limit);
      foreach ($rRuleOccurrences as $occ) {
        /** @var DateTimeInterface $occ */
        $occ = new Occurrence(
          Carbon::instance($occ),
          $this->isAllDay() || !$this->dateTimeEnd
            ? null
            : Carbon::instance($occ)->setTimeFromTimeString($this->dateTimeEnd),
          $this->allDay
        );
        array_push($occurrences, $occ);
      }
    } else {
      $occ = new Occurrence(
        $this->dtStart(),
        $this->isAllDay() || !$this->dateTimeEnd
          ? null
          : $this->dtStart()->copy()->setTimeFromTimeString($this->dateTimeEnd),
        $this->isAllDay()
      );
      array_push($occurrences, $occ);
    }

    usort($occurrences, [Occurrence::class, 'sort']);
    return $occurrences;
  }

  /**
   * @param int|null $limit
   * @return Occurrence[]
   */
  public function occurrencesAfter(Carbon|string $after, int $limit = null): array
  {
    /** @var Occurrence[] $occurrences */
    $occurrences = [];

    /* Skip if invalid */
    if (!$this->isValid()) return $occurrences;

    $after = $after instanceof Carbon ? $after : Carbon::parse($after);

    /* If event is not set to repeat and the event is before "after" */
    if (!$this->isRecurring() && $this->dtStart() < $after) return $occurrences;

    /* If the event is set to repeat but its "until" value is before "after" */
    if ($this->isRecurring() && $this->recurrenceUntil < $after) return $occurrences;

    if ($this->isRecurring()) {
      $rRuleOccurrences = $this->rRule()->getOccurrencesAfter($after, true, $limit);
      foreach ($rRuleOccurrences as $occ) {
        /** @var DateTimeInterface $occ */
        $occ = new Occurrence(
          Carbon::instance($occ),
          $this->isAllDay() || !$this->dateTimeEnd
            ? null
            : Carbon::instance($occ)->setTimeFromTimeString($this->dateTimeEnd),
          $this->allDay
        );
        array_push($occurrences, $occ);
      }
    } else {
      $occ = new Occurrence(
        $this->dtStart(),
        $this->isAllDay() || !$this->dateTimeEnd
          ? null
          : $this->dtStart()->copy()->setTimeFromTimeString($this->dateTimeEnd),
        $this->isAllDay()
      );
      array_push($occurrences, $occ);
    }

    usort($occurrences, [Occurrence::class, 'sort']);
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

    $from = $from instanceof Carbon ? $from : Carbon::parse($from);
    $to = $to instanceof Carbon ? $to : Carbon::parse($to);

    /* Skip if invalid */
    if (!$this->isValid()) return $occurrences;
    /* Skip if dateStart is after $to */
    if ($this->dtStart() > $to) return $occurrences;
    /* Skip if the event is set to repeat, and it should only repeat before $from */
    if ($this->isRecurring() && $this->recurrenceUntil < $from) return $occurrences;
    /* Skip if the event is not set to repeat, and it's "in the past" */
    if ($this->recurrenceFrequency === "none" && $this->dtStart() < $from) return $occurrences;

    if ($this->isRecurring()) {
      $rRuleOccurrences = $this->rRule()->getOccurrencesBetween($from, $to);
      foreach ($rRuleOccurrences as $occ) {
        /** @var DateTimeInterface $occ */
        $occ = new Occurrence(
          Carbon::instance($occ),
          $this->isAllDay() || !$this->dateTimeEnd
            ? null
            : Carbon::instance($occ)->setTimeFromTimeString($this->dateTimeEnd),
          $this->allDay
        );
        array_push($occurrences, $occ);
      }
    } else {
      $occ = new Occurrence(
        $this->dtStart(),
        $this->isAllDay() || !$this->dateTimeEnd
          ? null
          : $this->dtStart()->copy()->setTimeFromTimeString($this->dateTimeEnd),
        $this->isAllDay()
      );
      array_push($occurrences, $occ);
    }

    usort($occurrences, [Occurrence::class, 'sort']);
    return $occurrences;
  }
}
