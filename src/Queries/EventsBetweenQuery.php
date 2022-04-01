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

class EventsBetweenQuery extends Query
{

  use FiltersQuery;

  protected $attributes = [
    'name' => 'eventsBetween',
  ];

  public function type(): Type
  {
    $graphQLEntryTypeName = SettingsManager::graphQlTypeName();
    return GraphQL::type("[$graphQLEntryTypeName!]!");
  }

  public function args(): array
  {
    return [
      "from" => [
        "type" => GraphQL::type("String!"),
      ],
      "to" => [
        "type" => GraphQL::type("String!"),
      ],
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
      return $eventDates[$eventEntry->slug]->hasOccurrencesBetween($args["from"], $args["to"]);
    })->sort(function (EntryType $eventEntryA, EntryType $eventEntryB) use ($args, &$eventDates) {
      $occurrenceA = $eventDates[$eventEntryA->slug]->occurrencesBetween($args["from"], $args["to"])[0];
      $occurrenceB = $eventDates[$eventEntryB->slug]->occurrencesBetween($args["from"], $args["to"])[0];
      if ($occurrenceA->start === $occurrenceB->start) return 0;
      return $occurrenceA->start < $occurrenceB->start ? -1 : 1;
    });
  }
}
