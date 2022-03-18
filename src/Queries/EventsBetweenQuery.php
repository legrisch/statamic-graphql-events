<?php


namespace Legrisch\StatamicGraphlEvents\Queries;

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

class EventsBetweenQuery extends Query {

    use QueriesConditions;

    protected $attributes = [
        'name' => 'eventsBetween',
    ];

    public function type(): Type
    {
        return GraphQL::type("[Entry_Events_Event!]!");
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
            'sort' =>  GraphQL::type("String"),
        ];
    }

    public function resolve($root, $args)
    {

        $query = Entry::query();

        $query->where('collection', "events")->where('published', true);

        $this->filterQuery($query, $args['filter'] ?? []);
        $this->sortQuery($query, $args['sort'] ?? []);

        /** @var EventDates[] $eventDates */
        $eventDates = [];

        return $query->get()->filter(function (EntryType $eventEntry) use ($args, &$eventDates) {
            $eventDates[$eventEntry->slug()] = new EventDates($eventEntry->toAugmentedArray());
            return $eventDates[$eventEntry->slug()]->hasOccurrencesBetween($args["from"], $args["to"]);
        })->sort(function (EntryType $eventEntryA, EntryType $eventEntryB) use ($args, &$eventDates) {
            $occurrenceA = $eventDates[$eventEntryA->slug()]->occurrencesBetween($args["from"], $args["to"])[0];
            $occurrenceB = $eventDates[$eventEntryB->slug()]->occurrencesBetween($args["from"], $args["to"])[0];
            if ($occurrenceA->start === $occurrenceB->start) return 0;
            return $occurrenceA->start < $occurrenceB->start ? -1 : 1;
        });
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

    private function sortQuery($query, $sorts)
    {
        if (empty($sorts)) {
            $sorts = ['id'];
        }

        foreach ($sorts as $sort) {
            $order = 'asc';

            if (Str::contains($sort, ' ')) {
                [$sort, $order] = explode(' ', $sort);
            }

            $query->orderBy($sort, $order);
        }
    }
}
