<?php

namespace Legrisch\StatamicGraphQLEvents\RRule;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class OccurrenceType extends GraphQLType
{
  protected $attributes = [
    'name' => 'Occurrence',
    'description' => 'An event occurrence',
    'model' => Occurrence::class
  ];

  public function fields(): array
  {
    return [
      'start' => [
        'type' => Type::nonNull(Type::string()),
        'description' => 'The start of an event occurrence.',
        'resolve' => function (Occurrence $occ) {
          return $occ->start->toIso8601String();
        }
      ],
      'end' => [
        'type' => Type::string(),
        'description' => 'The end of an event occurrence.',
        'resolve' => function (Occurrence $occ) {
          if ($occ->end) {
            return $occ->end->toIso8601String();
          }
          return null;
        }
      ],
      'allDay' => [
        'type' => Type::nonNull(Type::boolean()),
        'description' => 'Whether an event takes place all day.',
        'resolve' => function (Occurrence $occ) {
          return $occ->allDay;
        }
      ],
    ];
  }
}

