# Database

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