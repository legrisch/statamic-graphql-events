<?php

namespace Legrisch\StatamicGraphQLEvents\Settings;

use Statamic\Contracts\Entries\QueryBuilder;
use Statamic\Entries\Collection;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Entry;
use Statamic\GraphQL\Types\EntryType;
use Statamic\Stache\Query\EntryQueryBuilder;
use Statamic\Support\FileCollection;

class SettingsManager
{
  private bool|null $isConfigured = null;
  private array|null $graphQLEntryTypeNames = null;
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

  public static function isConfigured(): bool
  {
    if (!is_null(SettingsManager::getInstance()->isConfigured)) {
      return SettingsManager::getInstance()->isConfigured;
    }
    $isConfigured = CollectionFacade::findByHandle(config('statamic.graphql-events.collection')) !== null;
    SettingsManager::getInstance()->isConfigured = $isConfigured;
    return SettingsManager::getInstance()->isConfigured;
  }

  public static function graphQlTypeNames(): array|null
  {
    if (!SettingsManager::isConfigured()) return null;

    if (SettingsManager::getInstance()->graphQLEntryTypeNames) {
      return SettingsManager::getInstance()->graphQLEntryTypeNames;
    }

    $collection = SettingsManager::collection();

    /** @var FileCollection $blueprints */
    $blueprints = $collection->entryBlueprints();

    $graphQLEntryTypeNames = $blueprints->map((function ($blueprint) use (&$collection) {
      return EntryType::buildName($collection, $blueprint);
    }))->all();

    SettingsManager::getInstance()->graphQLEntryTypeNames = $graphQLEntryTypeNames;
    return SettingsManager::getInstance()->graphQLEntryTypeNames;
  }

  public static function collection(): Collection|null
  {
    if (!SettingsManager::isConfigured()) return null;

    if (SettingsManager::getInstance()->collection) {
      return SettingsManager::getInstance()->collection;
    }
    $collection = CollectionFacade::findByHandle(config('statamic.graphql-events.collection'));
    SettingsManager::getInstance()->collection = $collection;
    return SettingsManager::getInstance()->collection;
  }

  public static function query(): QueryBuilder|EntryQueryBuilder|null
  {
    if (!SettingsManager::isConfigured()) return null;

    if (SettingsManager::getInstance()->queryBuilder) {
      return SettingsManager::getInstance()->queryBuilder;
    }

    $queryBuilder = Entry::query()
      ->where('collection', config('statamic.graphql-events.collection'));

    SettingsManager::getInstance()->queryBuilder = $queryBuilder;
    return $queryBuilder;
  }
}
