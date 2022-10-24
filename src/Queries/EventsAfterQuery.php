<?php


namespace Legrisch\StatamicGraphQLEvents\Queries;

use GraphQL\Type\Definition\Type;
use Legrisch\StatamicGraphQLEvents\RRule\EventDates;
use Legrisch\StatamicGraphQLEvents\Settings\SettingsManager;
use Statamic\Entries\Entry as EntryType;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Queries\Concerns\FiltersQuery;
use Statamic\GraphQL\Queries\Query;
use Statamic\GraphQL\Types\JsonArgument;

class EventsAfterQuery extends Query
{

  use FiltersQuery;

  protected $attributes = [
    'name' => 'eventsAfter',
  ];

  public function type(): Type
  {
    $graphQLEntryTypeNames = SettingsManager::graphQlTypeNames();
    if (count($graphQLEntryTypeNames) === 1) {
      return GraphQL::type("[$graphQLEntryTypeNames[0]!]!");
    }
    return GraphQL::type("[EntryInterface!]!");
  }

  public function args(): array
  {
    return [
      "after" => [
        "type" => GraphQL::type("String!"),
      ],
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

    return $query->get()->filter(function (EntryType $eventEntry) use ($args, &$eventDates) {
      $eventDates[$eventEntry->slug] = new EventDates($eventEntry->toAugmentedArray());
      return $eventDates[$eventEntry->slug]->hasOccurrencesAfter($args["after"]);
    })->sort(function (EntryType $eventEntryA, EntryType $eventEntryB) use ($args, &$eventDates) {
      $occurrenceA = $eventDates[$eventEntryA->slug]->occurrencesAfter($args["after"], 1)[0];
      $occurrenceB = $eventDates[$eventEntryB->slug]->occurrencesAfter($args["after"], 1)[0];
      if ($occurrenceA->start === $occurrenceB->start) return 0;
      return $occurrenceA->start < $occurrenceB->start ? -1 : 1;
    })->splice(0, $args["limit"] ?? null);
  }
}
