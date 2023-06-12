# Database

A lightweight wrapper around PDO/MySqli/SQLite with a consistent interface and includes helper functions to build and run queries quickly and easily.

For select statements and other complex queries you are expected to write raw paramatarised sql, using the "question mark" syntax.

## Installation

1. Install through composer (`composer require techwilk/database`)
2. Create database instance for either PDO / MySqli / SQLite

```php
use TechWilk\Database\MySqli\MySqliDatabase;

$database = new MySqliDatabase(
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