# Database

A lightweight wrapper around PDO/MySqli/SQLite with a consistent interface and includes helper functions to build and run queries quickly and easily.

For select statements and other complex queries you are expected to write raw parametrised sql, using the "question mark" syntax.

## Installation

1. Install through composer (`composer require techwilk/database`)
2. Create database instance for either PDO / MySqli / SQLite

```php
use TechWilk\Database\MySqli\MySqliDatabase;

$database = MySqliDatabase::connect(
    'localhost',
    'database-name',
    'username',
    'password',
);
```

## Select

Available functions:

- `query`
- `runQuery`

### Examples

#### No runtime parameters

```php
$result = $database->query('SELECT * FROM `users`');
$rows = $result->fetchAll();
var_dump($rows);
```

#### With runtime parameters

```php
$parameters = [1];
$result = $database->query('SELECT * FROM `users` WHERE id = ?', $parameters);
$row = $result->fetch();
var_dump($row);
```

#### Query generated elsewhere in code the codebase

```php
function customQueryBuilder() {
    $parameters = [1];
    $query = new Query(
        'SELECT * FROM `users` WHERE id = ?',
        $parameters,
    );
    return $query;
}

$query = customQueryBuilder();
$result = $database->runQuery($query);
$row = $result->fetch();
var_dump($row);
```

## Insert

Available functions:

- `insert`
- `insertOnDuplicate`
- `query`
- `runQuery`

### Create new simple record

```php
$data = [
    'id' => 3,
    'name' => 'Tim Jones',
    'auth_id' => 'xxx123yyy',
    'date_created' => '2022-03-03 00:00:00',
];
$id = $database->insert('users', $data);
var_dump($id);
```

### Create but handle potential key clash

```php
$data = [
    'name' => 'admin', // unique key
    'uses_count' => 1,
];
$onDuplicate = [
    'uses_count +' => 1, // += 1
];
$id = $database->insertOnDuplicate('tags', $data, $onDuplicate);
var_dump($id);
```

### Complex cross-table insert

```php
$sql = 'INSERT INTO users (`id`, `name`, `auth_id`, `date_created`) VALUES (?, ?, ?, ?)'
$parameters = [
    3, // id
    'Tim Jones', // name
    'xxx123yyy', // auth_id
    '2022-03-03 00:00:00', // date_created
];
$id = $database->query($sql, $parameters);
var_dump($id);
```

## Update

Available functions:

- `update`
- `updateUsingIn`
- `updateChanges`
- `selectAndUpdate`
- `query`
- `runQuery`

```php
$data = [
    'name' => 'Timothy Jones',
];
$rowCount = $database->update('users', $data, ['id' => 3]);
var_dump($rowCount);
```

## Delete

Available functions:

- `delete`
- `deleteUsingIn`
- `query`
- `runQuery`

```php
$rowCount = $database->delete('table', ['id' => 3]);
var_dump($rowCount);
```

## Exceptions

This library will always throw exceptions when an error is encountered regardless of the database driver's configuration (database errors are converted into exceptions when necessary).

Exceptions live under the namespace `TechWilk\Database\Exception\`. All exceptions inherit from `DatabaseException` (the root) and are grouped underneath in two levels of hierarchy (root, parent, leaf).
Where possible these parent groupings align with the SQLSTATE categorisation, however a few leaf errors have been recategorised to a more appropriate parent.

Catch a category **parent** when you care about intent, an error of that type (e.g. `IntegrityConstraintException`, `DatabaseQueryException`, `DatabaseConnectionException`).

Catch a specific **leaf** when you need to perform logic on the specific error (e.g. `DuplicateDatabaseRecordException`, `EmptyQueryException`, `InvalidTableException`).

Each exception includes it's `$previous` driver specific exception in case you need to access it (though only if the driver actually threw the exception).
Exception `getCode()` is the vendor errno integer from the specific database driver, whereas `getSqlState()` is the SQLSTATE string.
Most driver codes are available on the exception classes as class constants for your convenience and to avoid the need for magic numbers in your code.

To learn more, see `TechWilk\Database\DatabaseErrorMapper`.

### Hierarchy

- `DatabaseException`
    - `DatabaseAccessDeniedException`
    - `DatabaseConnectionException`
    - `DatabaseDataException`
    - `DatabaseFeatureNotSupportedException`
    - `DatabaseLimitExceededException`
    - `DatabaseObjectExistsException`
    - `DatabaseObjectNotFoundException`
        - `InvalidTableException`
    - `DatabaseQueryException`
        - `BadFieldException`
        - `BadValueException`
        - `EmptyQueryException`
    - `DatabaseTransactionException`
        - `DatabaseReadOnlyException`
    - `DatabaseTransactionRollbackException`
        - `DatabaseDeadlockException`
    - `IntegrityConstraintException`
        - `DuplicateDatabaseRecordException`

### Notes

**Migration note (PDO):** versions higher than 1.0.3 include a fix for an error handling bug with our PDO wrapper (mysqli was unaffected) where `DuplicateDatabaseRecordException` was thrown for any `IntegrityConstraintException` rather than just duplicate records, and `EmptyQueryException` was thrown for any "bad sql". If you were relying on this behaviour please now use the `IntegrityConstraintException` and `DatabaseQueryException` category parents respectively.


---

## Testing

- copy `phpunit.xml.dist` to `phpunit.xml`
- fill out the environment details
- run `composer test`

### Testing environment

- requires a copy of each database available

#### MySQL

- `podman network create database-tests`

- ```podman run --name database-percona -p 3306 -e MYSQL_ROOT_PASSWORD="change-to-secure-password-here" --net database-tests -d docker.io/library/percona:8.0```

- `podman exec -it database-percona mysql -uroot -p`
- mysql> `CREATE DATABASE tests;`
- mysql> ```CREATE USER `tests`@`%` IDENTIFIED BY 'create-random-password-here';```
- mysql> ```GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,ALTER ON tests.* TO `tests`@`%`;```
- mysql> `FLUSH PRIVILEGES;`
- mysql> `exit`

- ensure you make a note of which port the db is being exposed on (using `podman ps`). This is likely a large number, such as `44449`