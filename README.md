# Foundation

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pomm-project/Foundation/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pomm-project/Foundation/?branch=master) [![Build Status](https://travis-ci.org/pomm-project/Foundation.svg?branch=master)](https://travis-ci.org/pomm-project/Foundation)

This is the foundation component for the Pomm database framework. It works only with PHP >= 5.4.4.

Pomm Foundation is in alpha state, code may change without notice. If you are looking for a stable library, look at [Pomm 1.x](http://www.pomm-project.org).

## Installation

Pomm components are available on [packagist](https://packagist.org/packages/pomm-project/) using [composer](https://packagist.org/). To install and use Pomm's foundation, add a require line to `"pomm-project/foundation"` in your `composer.json` file.

## What is Foundation ?

It is the main block of Pomm database framework. It handles connection configuration and sessions. If you are looking for a library to use Postgresql in your web development, you might want to look at [Pomm's model manager](https://github.com/pomm-project/ModelManager). If you want to create a database access layer, Foundation is the right tool.

The easiest way to open a connection to the database server:

```php
<?php

/*
 ... autoloading stuff ..
*/

use PommProject\Foundation\Pomm;

// instantiate the service with the configuration as parameter:
$pomm = new Pomm(['my_db' => ['dsn' => 'pgsql://user:pass@host:port/db_name']]);

// get a session from the service:
$session = $pomm['my_db'];
```

## Sessions, clients and poolers

The `Session` instance is the keystone of Foundation. It is a client manager for the database connection handler. A client is a class that needs to interact with the database. It registers to the Session so the session injects into it. As soon as a client is registered, it gets access to the database connection and all other clients in the same time. Furthermore, the session does shutdown all the clients properly when going down which may be useful if clients rely on database structure (prepared queries, temporary tables etc.).

All model files are in a way clients of a session. By example, converter classes or prepared statements are clients of a session. To use a client from the session, call the `$session->getClient()` method. The problem here is that when no client is found, null is returned. To manage clients creation in the pool, `ClientPooler` can be registered. Most of the time, they check in the pool to see if the asked client is registered, if not they instantiate it, register it and send it back. It is possible to ask for a client through a `ClientPooler` using `$session->getClientUsingPooler()` method.

```php
<?php
// ...

$pomm = new Pomm(['my_db' => ['dsn' => 'pgsql://user:pass@host:port/db_name']]);
$pomm['my_db']
    ->getPoolerForType('prepared_query')
    ->getClient('select * from student where age > $*')
    ->execute([20])
    ;
```

For convenience, it is possible to request a client through a pooler using Session's `__call` method:

```php
<?php
// ...

$pomm = new Pomm(['my_db' => ['dsn' => 'pgsql://user:pass@host:port/db_name']]);
$pomm['my_db']
    ->getPreparedQuery('select * from student where age > $*')
    ->execute([20])
    ;
```

The point here is to understand that the instantiated clients are automatically reused when they are called several times. Clients are shutdown properly when the session is destroyed by PHP. The second strong point of this system is that all clients own a pointer to the session. So it can use other clients from it.

## Querying and converter system.

In foundation, performing a query and return a result as an iterator is a Client too:

```php
<?php
//…
$pomm = new Pomm(['my_db' => ['dsn' => 'pgsql://user:pass@host:port/db_name']]);
$iterator = $pomm['my_db']
    ->getQuery()
    ->query('select * from student where age > $*', [20])
    ;

foreach($iterator as $student) {
    printf("Student id = '%d', age = '%d', name = '%s'.\n",
        $student['id'],
        $student['age'],
        $student['name']
        );
}
```

The `query` method returns an iterator on results. Data can then be fetched on demand from the database. All values are **converted** using Foundation converter system. It is also possible to fetch column oriented results instead of row oriented using the `slice()` method:

```php
<?php
//…
$pomm = new Pomm(['my_db' => ['dsn' => 'pgsql://user:pass@host:port/db_name']]);
$iterator = $pomm['my_db']
    ->getQuery()
    ->query('select * from student where age > $*', [20]);

printf("Names are: %s.\n", join(', ', $iterator->slice('name')));
```

It is possible to use your own Query class as soon as it implements `QueryInterface`:

```php
$result = $pomm['my_db']
    ->getQuery(`My\Query\Class')
    ->query('select * from student where age > $*', [20])
    ;
```

## Tests

This package uses Atoum as unit test framework. The tests are located in `sources/tests`. The test suite needs to access the database to ensure read and write operations are made in a consistent manner. You need to set up a database for that and fill the `sources/tests/config.php` file with the according DSN. For convenience, it is possible to extend class `SessionAwareAtoum` to get a `getSession()` method in the test class. Since the returned session is blank, this class defines an abstract method `initializeSession()` where it is possible to register needed poolers and clients for the tests.
