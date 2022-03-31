<?php

namespace Legrisch\StatamicGraphQLEvents\Settings;

use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Entries\Collection;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\GraphQL\Types\EntryType;
use Statamic\Stache\Query\EntryQueryBuilder;
use Statamic\Support\FileCollection;

class SettingsManager
{
  private string|null $graphQLEntryTypeName = null;
  private Collection|null $collection = null;
  private QueryBuilder|EntryQueryBuilder|null $queryBuilder = null;

  private function __construct()
  {
  }

  private static function getInstance(): SettingsManager
  {
    static $inst = null;
    if ($inst === null) {
      $inst = new SettingsManager();
    }
    return $inst;
  }

  public static function graphQlTypeName(): string
  {
    if (SettingsManager::getInstance()->graphQLEntryTypeName) {
      return SettingsManager::getInstance()->graphQLEntryTypeName;
    }

    $collection = SettingsManager::collection();

    /** @var FileCollection $blueprints */
    $blueprints = $collection->entryBlueprints();
    $blueprintHandle = config('statamic.graphql-events.blueprint');
    $blueprint = $blueprints->where('handle', $blueprintHandle)->first();
    $graphQLEntryTypeName = EntryType::buildName($collection, $blueprint);
    SettingsManager::getInstance()->graphQLEntryTypeName = $graphQLEntryTypeName;

    return SettingsManager::getInstance()->graphQLEntryTypeName;
  }

  public static function collection(): Collection
  {
    if (SettingsManager::getInstance()->collection) {
      return SettingsManager::getInstance()->collection;
    }
    $collection = CollectionFacade::findByHandle(config('statamic.graphql-events.collection'));
    SettingsManager::getInstance()->collection = $collection;
    return SettingsManager::getInstance()->collection;
  }

  public static function query(): QueryBuilder|EntryQueryBuilder
  {
    if (SettingsManager::getInstance()->queryBuilder) {
      return SettingsManager::getInstance()->queryBuilder;
    }
    $collection = SettingsManager::collection();
    $query = $collection->queryEntries();
    $queryBuilder = $query->where('blueprint', config('statamic.graphql-events.blueprint'))->where('published', true);
    SettingsManager::getInstance()->queryBuilder = $queryBuilder;
    return $queryBuilder;
  }
}
