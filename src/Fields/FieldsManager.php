<?php

namespace Legrisch\StatamicGraphQLEvents\Fields;

use Carbon\Carbon;
use Legrisch\StatamicGraphQLEvents\RRule\EventDates;
use Legrisch\StatamicGraphQLEvents\Settings\SettingsManager;
use Statamic\Entries\Entry;
use Statamic\Facades\GraphQL;

class FieldsManager
{
  public static function addFields()
  {

    $graphQLEntryTypeName = SettingsManager::graphQlTypeName();

    GraphQL::addField($graphQLEntryTypeName, 'occurrences', function () {
      return [
        "type" => GraphQL::type('[Occurrence!]!'),
        'args' => [
          "limit" => [
            "type" => GraphQL::type("Int"),
          ],
        ],
        "resolve" => function (Entry $entry, $args) {
          $eventDates = new EventDates($entry->toAugmentedArray());
          return $eventDates->occurrences($args['limit'] ?? null);
        }
      ];
    });

    GraphQL::addField($graphQLEntryTypeName, 'occurrencesAfter', function () {
      return [
        "type" => GraphQL::type('[Occurrence!]!'),
        'args' => [
          "after" => [
            "type" => GraphQL::type("String!"),
          ],
          "limit" => [
            "type" => GraphQL::type("Int"),
          ],
        ],
        "resolve" => function (Entry $entry, $args) {
          $eventDates = new EventDates($entry->toAugmentedArray());
          return $eventDates->occurrencesAfter($args["after"], $args["limit"] ?? null);
        }
      ];
    });

    GraphQL::addField($graphQLEntryTypeName, 'occurrencesBetween', function () {
      return [
        "type" => GraphQL::type('[Occurrence!]!'),
        'args' => [
          "from" => [
            "type" => GraphQL::type("String!"),
          ],
          "to" => [
            "type" => GraphQL::type("String!"),
          ],
        ],
        "resolve" => function (Entry $entry, $args) {
          $eventDates = new EventDates($entry->toAugmentedArray());
          return $eventDates->occurrencesBetween($args["from"], $args["to"]);
        }
      ];
    });

    GraphQL::addField($graphQLEntryTypeName, 'occurrencesAfterNow', function () {
      return [
        "type" => GraphQL::type('[Occurrence!]!'),
        'args' => [
          "limit" => [
            "type" => GraphQL::type("Int"),
          ],
        ],
        "resolve" => function (Entry $entry, $args) {
          $eventDates = new EventDates($entry->toAugmentedArray());
          return $eventDates->occurrencesAfter(Carbon::now(), $args["limit"] ?? null);
        }
      ];
    });

    GraphQL::addField($graphQLEntryTypeName, 'humanReadableRRules', function () {
      return [
        "type" => GraphQL::type('[String!]!'),
        'args' => [
          "locale" => [
            "type" => GraphQL::type("String!"),
          ],
          "includeStart" => [
            "type" => GraphQL::type("Boolean"),
            "description" => "Whether to include the start date, defaults to false"
          ]
        ],
        "resolve" => function (Entry $entry, $args) {
          /** @var string[] $strings */
          $strings = [];
          $eventDates = new EventDates($entry->toAugmentedArray());
          foreach ($eventDates->eventDates as $eventDate) {
            $rrule = $eventDate->rRule();
            $rDate = $eventDate->rDate();
            if ($rrule) {
              $string = $rrule->humanReadable([
                "locale" => $args['locale'],
                "fallback" => "en",
                "include_start" => $args['includeStart'] ?? false,
              ]);
              array_push($strings, $string);
            } else if ($rDate) {
              $prefix = $args['locale'] === "en" ? "On" : "Am";
              $dateString = $rDate->copy()->locale($args['locale'])->isoFormat('L');
              $string = "$prefix $dateString";
              array_push($strings, $string);
            }
          }
          return $strings;
        }
      ];
    });
  }
}
