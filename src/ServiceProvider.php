<?php

namespace Legrisch\StatamicGraphQLEvents;

use Legrisch\StatamicGraphQLEvents\Fields\FieldsManager;
use Legrisch\StatamicGraphQLEvents\Queries\EventsAfterNowQuery;
use Legrisch\StatamicGraphQLEvents\Queries\EventsAfterQuery;
use Legrisch\StatamicGraphQLEvents\Queries\EventsBetweenQuery;
use Legrisch\StatamicGraphQLEvents\RRule\OccurrenceType;
use Statamic\Facades\GraphQL;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
  public function register()
  {
    $this->mergeConfigFrom(
      __DIR__ . '/../config/config.php', 'statamic.graphql-events'
    );
  }

  public function bootAddon()
  {
    $this->publishes([
      __DIR__ . '/../config/config.php' => config_path('statamic/graphql-events.php'),
      __DIR__ . '/../fieldset/dates.yaml' => resource_path('fieldsets/dates.yaml'),
    ], 'statamic.graphql-events');

    GraphQL::addType(OccurrenceType::class);

    GraphQL::addQuery(EventsAfterNowQuery::class);
    GraphQL::addQuery(EventsAfterQuery::class);
    GraphQL::addQuery(EventsBetweenQuery::class);

    FieldsManager::addFields();
  }
}
