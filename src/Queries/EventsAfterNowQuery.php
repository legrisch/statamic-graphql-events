<?php


namespace Legrisch\StatamicGraphQLEvents\Queries;

use Carbon\Carbon;
use GraphQL\Type\Definition\Type;
use Legrisch\StatamicGraphQLEvents\RRule\EventDates;
use Legrisch\StatamicGraphQLEvents\Settings\SettingsManager;
use Statamic\Entries\Entry;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Queries\Concerns\FiltersQuery;
use Statamic\GraphQL\Queries\Query;
use Statamic\GraphQL\Types\JsonArgument;

class EventsAfterNowQuery extends Query
{

  use FiltersQuery;

  protected $attributes = [
    'name' => 'eventsAfterNow',
  ];

  public function type(): Type
  {
    $graphQLEntryTypeName = SettingsManager::graphQlTypeName();
    return GraphQL::type("[$graphQLEntryTypeName!]!");
  }

  public function args(): array
  {
    return [
      "limit" => GraphQL::type("Int"),
      'filter' => GraphQL::type(JsonArgument::NAME),
    ];
  }

  public function resolve($root, $args)
  {
    $query = SettingsManager::query();

    $this->filterQuery($query, $args['filter'] ?? []);

    /** @var EventDates[] $eventDates */
    $eventDates = [];

    return $query->get()->filter(function (Entry $eventEntry) use ($args, &$eventDates) {
      $eventDates[$eventEntry->slug] = new EventDates($eventEntry->toAugmentedArray());
      return $eventDates[$eventEntry->slug]->hasOccurrencesAfter(Carbon::now());
    })->sort(function (Entry $eventEntryA, Entry $eventEntryB) use ($args, &$eventDates) {
      $occurrenceA = $eventDates[$eventEntryA->slug]->occurrencesAfter(Carbon::now(), 1)[0];
      $occurrenceB = $eventDates[$eventEntryB->slug]->occurrencesAfter(Carbon::now(), 1)[0];
      if ($occurrenceA->start === $occurrenceB->start) return 0;
      return $occurrenceA->start < $occurrenceB->start ? -1 : 1;
    })->splice(0, $args["limit"] ?? null);
  }
}
