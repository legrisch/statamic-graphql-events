# Statamic GraphQL Events

Statamic GraphQL Events is a [Statamic](https://statamic.com/) addon that provides a fieldset and the corresponding GraphQL Queries and fields to handle recurring events with ease.

---

## Features

- GraphQL queries & fields for Recurring events
- Infinitely complex recurrence rules
- Easy to set up

## How to Install

``` bash
composer require legrisch/statamic-graphql-events
```

## Setup

> This addon assumes that you want to use a single collection and entries of a single blueprint as the source of your events.

- Run `php artisan vendor:publish --tag=statamic.graphql-events --force` to publish the addon configuration along with the provided fieldset "Dates".
- Add the provided fieldset "Dates" to a blueprint. You may translate the fields to your liking, be sure to keep the
  handles.
- Edit the addon configuration: `config/statamic/graphql-events.php`.
- Open the GraphiQL Editor.
- You should see three new queries: `eventsAfter`, `eventsAfterNow` and `eventsBetween`.

## How to Use

### Queries

> Fields and Queries that use dates as an input accept everything that `Carbon::parse` accepts.

All query results are sorted by the first occurrence in the given timeframe.

#### `eventsAfter`

Returns events that have occurrences after a certain date.

##### Example

```graphql
query MyQuery {
  eventsAfter(after: "1. April 2022") {
    title
    slug
    occurrencesAfter(after: "1. April 2022") {
      start
    }
  }
}
```

#### `eventsAfterNow`

Returns events that have occurrences after now.

##### Example

```graphql
query MyQuery {
  eventsAfterNow {
    slug
    title
    occurrencesAfterNow {
      start
    }
  }
}
```

#### `eventsBetween`

Returns events that have occurrences between two provided dates.

##### Example

```graphql
query MyQuery {
  eventsBetween(from: "1. March 2022", to: "1. April 2022") {
    slug
    title
    occurrencesBetween(from: "1. March 2022", to: "1. April 2022") {
      start
    }
  }
}
```

### Fields

Besides the queries, this addon adds fields to the GraphQL type of your entries.

#### `occurrences`

Returns the occurrences of an event starting from the first occurrence.

#### Example

```graphql
occurrences(limit: 10) {
  allDay
  end
  start
}
```

#### `occurrencesAfter`

Returns the occurrences of an event starting from a provided date.

#### Example

```graphql
occurrencesAfter(after: "1. April 2022", limit: 10) {
  allDay
  end
  start
}
```

#### `occurrencesAfterNow`

Returns the occurrences of an event starting from now.

#### Example

```graphql
occurrencesAfterNow(limit: 10) {
  allDay
  end
  start
}
```

#### `occurrencesBetween`

Returns the occurrences of an event between two provided dates.

#### Example

```graphql
occurrencesBetween(from: "1. March 2022", to: "1. April 2022") {
  start
  allDay
  end
}
```

---

## License

This project is licensed under the MIT License.
