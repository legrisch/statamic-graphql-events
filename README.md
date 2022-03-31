# Statamic GraphQL Events <!-- omit in toc -->

Statamic GraphQL Events is a [Statamic](https://statamic.com/) addon that provides a fieldset and the corresponding GraphQL Queries and fields to handle recurring events with ease.

- GraphQL queries & fields for recurring events
- Infinitely complex recurrence rules
- Easy to set up

### Index <!-- omit in toc -->

- [Install](#install)
- [Setup](#setup)
- [How to Use](#how-to-use)
  - [Queries](#queries)
    - [`eventsAfter`](#eventsafter)
    - [`eventsAfterNow`](#eventsafternow)
    - [`eventsBetween`](#eventsbetween)
  - [Fields](#fields)
    - [`occurrences`](#occurrences)
    - [`occurrencesAfter`](#occurrencesafter)
    - [`occurrencesAfterNow`](#occurrencesafternow)
    - [`occurrencesBetween`](#occurrencesbetween)
- [License](#license)

## Install

``` bash
composer require legrisch/statamic-graphql-events
```

## Setup

> This addon assumes that you want to use a single collection and entries of a single blueprint as the source of your events.

- Run `php artisan vendor:publish --tag=statamic.graphql-events --force` to publish the configuration along with the provided fieldset "Dates".
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

##### Example <!-- omit in toc --> <!-- omit in toc -->

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

##### Example <!-- omit in toc --> <!-- omit in toc -->

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

##### Example <!-- omit in toc --> <!-- omit in toc -->

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

##### Example <!-- omit in toc -->

```graphql
occurrences(limit: 10) {
  start
  end
  allDay
}
```

#### `occurrencesAfter`

Returns the occurrences of an event starting from a provided date.

##### Example <!-- omit in toc -->

```graphql
occurrencesAfter(after: "1. April 2022", limit: 10) {
  start
  end
  allDay
}
```

#### `occurrencesAfterNow`

Returns the occurrences of an event starting from now.

##### Example <!-- omit in toc -->

```graphql
occurrencesAfterNow(limit: 10) {
  start
  end
  allDay
}
```

#### `occurrencesBetween`

Returns the occurrences of an event between two provided dates.

##### Example <!-- omit in toc -->

```graphql
occurrencesBetween(from: "1. March 2022", to: "1. April 2022") {
  start
  end
  allDay
}
```

---

## License

This project is licensed under the MIT License.
