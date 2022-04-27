Repl.it PHP Database Client
============


Simple Repl.it database client in PHP. Loosely based off of the [replit/database-node](https://github.com/replit/database-node).

[![Run on Repl.it](https://repl.it/badge/github/fatfingers23/Replit-Database-Client)](https://repl.it/github/fatfingers23/Replit-Database-Client)

Requirements
------------

* PHP >= 8.0;
* Composer.

Installation
============

`composer require fatfingers23/replit-database-client`

## Get started
```php
<?php

require_once 'vendor/autoload.php';

use Fatfingers23\ReplitDatabaseClient\DatabaseClient;

$client = new DatabaseClient();
$client->set('key', 'value');
$key = $client->get('key');
echo $key;
```
## Docs
Client
> ### `class DatabaseClient(String url?)`
Ability to pass a custom url

**Native Functions**

> `set(string $key, array|string $value): void`

Sets a key with a string value
```php
<?php
$client->set('key', 'value');
```

Sets a key with an array value
```php
<?php
$client->set('key', ['greeting' => 'Hello World!']);
```

> `get(string $key): array|string|null`
 
Gets a key with a string value
```php
<?php
$key = $client->get('key');
echo $key;
```

Gets a key with an array value
```php
<?php
$key = $client->get('key');
var_dump($key);
echo $key['greeting'];
```

> `delete(string $key): void`
 
Deletes an entry in the database by its key
```php
<?php
$client->delete('key');
```

> `getPrefixKeys(string $prefix): array`
* Returns an array of keys that start with the prefix
* If no prefix is given returns all the keys in the database
* Returns an empty array if it finds no keys 
```php
<?php
$client->set('poet.1', 'John Keats');
$client->set('poet.2', 'Emily Dickinson');
$poetKeys = $client->getPrefixKeys('poet');

#var_export($poetKeys) result below
array (
  0 => 'poet.1',
  1 => 'poet.2',
)
```

**Extended Functions**

> `getPrefix(string $prefix): ?array`
* Returns an array of all the values to a prefix
* If no prefix is given returns the whole database
* Returns null if no prefixs are found
```php
<?php
$client->set('poet.1', 'John Keats');
$client->set('poet.2', 'Emily Dickinson');
$poets = $client->getPrefix('poet');

#var_export($poets) result below
array (
  'poet.1' => 'John Keats',
  'poet.2' => 'Emily Dickinson',
)
```

> `deleteByPrefix(string $prefix = '')`
* Deletes a series of keys by their prefix
* Careful if no prefix is given, this function will delete the entire database
```php
<?php
$client->deleteByPrefix('poet');
```


## Tests
```sh
composer test
```
or can just click run if this is open in a Repl.it


Contributing
============

1. Fork it.
2. Create your feature branch (git checkout -b my-new-feature).
3. Make your changes.
4. Run the tests, adding new ones for your own code if necessary (phpunit).
5. Commit your changes (git commit -am 'Added some feature').
6. Push to the branch (git push origin my-new-feature).
7. Create new pull request.

License
=======

Please refer to [LICENSE](https://github.com/GinoPane/composer-package-template/blob/master/LICENSE).