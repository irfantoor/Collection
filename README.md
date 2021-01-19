# IrfanTOOR\\Collection

Collection implementing ArrayAccess, Countable and IteratorAggregate

The identifiers can use dot notation to access an identifier down a hierarchical
level, e.g. to access ```$config['debug']['log']['file']``` you can code in
doted notation as: ```$config['debug.log.file']```

## Initializing

You can initialize by passing an array of key value pairs while creating a new instance.

```php
<?php

use IrfanTOOR\Collection;

$init = [
	'app' => [
		'name' => 'My App',
		'version' => '1.1',
	],
	'debug' => [
		'level' => 1,
		'log'   => [
			'enabled' => 0,
			'channel' => 'DEBUG',
			'file'    => '/tmp/debug.log',
		],
	]
];

$app = new IrfanTOOR\Collection($init);
```

## Setting

You can by set an identifier in the collection by using the method 'set':

```php
use IrfanTOOR\Collection;

$app = new IrfanTOOR\Collection();

# setting hello => world
$app->set('hello', 'world');

# using an array notation
$app['hello'] = 'world';

# defining multiple
$app->setMultiple([
  'hello'     => 'world',
  'box'       => 'empty',
  'something' => null,
  'array'     => [
    'action'       => 'go',
    'step'         => 1,
  ],
]);

# defining sub values
$app->set('app.name', 'Another App');
$app->set('debug.level', 2);
```

or by using array access mechanism:

```php
$app['hello'] = 'world';
$app['debug.log.file'] = '/my/debug/log/file.log';
```

## Getting

You can get the stored value in the collection by its identifier using the
method 'get':

```php
$debug_level = $app->get('debug.level');

# returns the value stored against this identifier or returns 0 if the identifier
# is not present in the collection
$debug_log_level = $app->get('debug.log.level', 0);
```
you can also use the array access:

```php
$debug_level = $app['debug.level'];

# returns the value stored against this identifier or returns 0 if the identifier
# is not present in the collection
$debug_log_level = $app['debug.log.level'] ?? 0;
```

## Checking if a value is present in the collection

You can use the method 'has' to check if the collection has an entry identified
with the identifier id:

```php
if ($app->has('debug.level')) {
  # this will be executed even if the given identifier has the value NULL, 0
  # or false
  echo 'debug.level is present in the collection'
}
```

using the array access the above code will become:

```php
if (isset($app['debug.level']) {
  # this will be executed even if the given identifier has the value NULL, 0
  # or false
  echo 'debug.level is present in the collection'
}
```

## Removing an entry

You can use the method 'remove' or unset on the element:

```php
# using method remove
$app->remove('debug.level');

# using unset
unset($app['debug.level']);
```

## Collection to Array

The method 'toArray' can be used to convert the collection into an array:

```php
$array = $app->toArray();
```

## Array of identifiers
The array of identifiers can be retrieved by using the method 'keys':

```php
$ids_array = $app->keys();
```

## Count

The number of items present in the collection can be retrieved using the method
'count'. Note that it will return the count of the items at base level.

```php
# will return 2 for the Collection defined in initialization section
$count = $app->count();
```

## Iteration

The collection can directly be used in a foreach loop or wherever an iterator
is used. for example the code:

```php
foreach($app->toArray() as $k => $v)
{
    print_r([$k => $v]);
}
```

can be simplified as:

```php
foreach($app as $k => $v)
{
    print_r([$k => $v]);
}
```

## Lock

A collection can be made read only when once it is initialised by using the lock function, the values can not be added, modified, or removed from the collection afterwards.

Use case for a readonly collection is configuration of an application.

e.g.

```php
$config = new IrfanTOOR\Collection([
  'app' => [
    'name' => 'My App',
    'version' => '1.1',
  ],
  'debug' => [
    'level' => 1,
    'log'   => [
      'enabled' => 0,
      'channel' => 'DEBUG',
      'file'    => '/tmp/debug.log',
    ],
  ]
]);

$config->lock();

# will not modify the values
$config->set('debug.level', 0);
$config->set('app.version', '1.2');

# will not remove the value
$config->remove('debug.log');
```

## Utility fuctions

### filter

Returns the collection of the elements which return true

NOTE: $callback function must uses parameters in the following order
for the provided callback function:
  param_1 $value Value of the current element
  param_2 $key   Key of the current element
   
```php
  $c = new Collection(['10', 1, 2, 3, 4]);

  $callback = function ($key, $value) {
    return is_int($value);
  };

  $int = $c->filter($callback); # 1, 2, 3, 4
```

### map

Returns a collection with the callback applied to the element values
of this collection:

```php
  $callback = function ($key, $value) {
    return $value * $value;
  };

  # int is the collection from previous example
  $int2 = $int->map($callback); # 1, 4, 9, 16
```

### reduce

Reduces the array to a result, by applying the function to all of its elements

NOTE: $callback function must uses parameters in the following order:
  param_1 $carry Result of callback operation on the previous element
  param_2 $value Value of the current element
  param_3 $key   Key of the current element

```php
  $callback = function ($key, $value, $carry) {
    return $carry + $value;
  };

  $sum = $int2->reduce($callback); # 30
```
