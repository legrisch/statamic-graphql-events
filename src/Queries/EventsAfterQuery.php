<?php


namespace Legrisch\StatamicGraphlEvents\Queries;

use Carbon\Carbon;
use GraphQL\Type\Definition\Type;
use Legrisch\StatamicGraphlEvents\RRule\EventDates;
use Statamic\Facades\Entry;
use Statamic\Entries\Entry as EntryType;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Queries\Query;
use Statamic\GraphQL\Types\JsonArgument;
use Statamic\Tags\Concerns\QueriesConditions;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class EventsAfterQuery extends Query {

    use QueriesConditions;

    protected $attributes = [
        'name' => 'eventsAfter',
    ];

    public function type(): Type
    {
        return GraphQL::type("[Entry_Events_Event!]!");
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

        $query = Entry::query();

        $query->where('collection', "events")->where('published', true);

        $this->filterQuery($query, $args['filter'] ?? []);

        /** @var EventDates[] $eventDates */
        $eventDates = [];

        return $query->get()->filter(function (EntryType $eventEntry) use ($args, &$eventDates) {
            $eventDates[$eventEntry->slug()] = new EventDates($eventEntry->toAugmentedArray());
            return $eventDates[$eventEntry->slug()]->hasOccurrencesAfter($args["after"]);
        })->sort(function (EntryType $eventEntryA, EntryType $eventEntryB) use ($args, &$eventDates) {
            $occurrenceA = $eventDates[$eventEntryA->slug()]->occurrencesAfter($args["after"], 1)[0];
            $occurrenceB = $eventDates[$eventEntryB->slug()]->occurrencesAfter($args["after"], 1)[0];
            if ($occurrenceA->start === $occurrenceB->start) return 0;
            return $occurrenceA->start < $occurrenceB->start ? -1 : 1;
        })->splice(0, $args["limit"] ?? null);
    }

    private function filterQuery($query, $filters)
    {
        if (! isset($filters['status']) && ! isset($filters['published'])) {
            $filters['status'] = 'published';
        }

        foreach ($filters as $field => $definitions) {
            if (! is_array($definitions)) {
                $definitions = [['equals' => $definitions]];
            }

            if (Arr::assoc($definitions)) {
                $definitions = collect($definitions)->map(function ($value, $key) {
                    return [$key => $value];
                })->values()->all();
            }

            foreach ($definitions as $definition) {
                $condition = array_keys($definition)[0];
                $value = array_values($definition)[0];
                $this->queryCondition($query, $field, $condition, $value);
            }
        }
    }
}
