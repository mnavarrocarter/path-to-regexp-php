MNC PathToRegExpPHP
===================

[![Actions Status](https://github.com/mnavarrocarter/path-to-regexp-php/workflows/CI/badge.svg)](https://github.com/mnavarrocarter/path-to-regexp-php/actions)

Turns an Express-style path string such as `/user/:name` into a regular expression,
so it can be used in routing engines.

This is an Object Oriented port of the famous JS library `path-to-regexp`, used by Node's Express
and other js frameworks.

The hard work of porting the JS library to PHP was done originally done by Gil Polguère. He did the hard part.

I just added the following features:
1. Bumped PHP to 7.2 minimum
2. Added type hints where possible
3. Removed all the arrays passed by reference and used objects to store that state instead.

## Usage

```php
use MNC\PathToRegExpPHP\PathRegExpFactory;

$pathRegex = PathRegExpFactory::create('/user/:name');
$result = $pathRegex->match('/user/john');
$result->getMatchedString();    // '/user/john'
$result->getValues();           // ['name' => 'john']
```

You have several flags you can pass as options of the `create` method:
- `PathRegExpFactory::CASE_SENSITIVE`: When passed the route will be treated as case sensitive.
- `PathRegExpFactory::STRICT`: When passed a slash is allowed to be trailing the path.
- `PathRegExpFactory::END`: When **not** passed the path will match at the beginning.

By default, the only flag enabled in the create method is `PathRegExpFactory::END`.

### Parameters

The path has the ability to define parameters and automatically populate the keys array.

#### Named Parameters

Named parameters are defined by prefixing a colon to the parameter name (`:foo`).
By default, this parameter will match up to the next path segment.

```php
use MNC\PathToRegExpPHP\PathRegExpFactory;

$pathRegex = PathRegExpFactory::create('/:foo/:bar');
$pathRegex->getParts()[0]->getName(); // 'foo'
$pathRegex->getParts()[1]->getName(); // 'bar'

$result = $pathRegex->match('/test/route');
$result->getMatchedString();    // '/test/route'
$result->getValues();           // ['foo' => 'test', 'bar' => 'route']
```

#### Suffixed Parameters

##### Optional

Parameters can be suffixed with a question mark (`?`) to make the entire parameter optional.
This will also make any prefixed path delimiter optional (`/` or `.`).

```php
use MNC\PathToRegExpPHP\PathRegExpFactory;

$pathRegex = PathRegExpFactory::create('/:foo/:bar?');

$result = $pathRegex->match('/test');
$result->getMatchedString();    // '/test'
$result->getValues();           // ['foo' => 'test', 'bar' => null]

$result = $pathRegex->match('/test/route');
$result->getMatchedString();    // '/test/route'
$result->getValues();           // ['foo' => 'test', 'bar' => 'route']
```

##### Zero or more

Parameters can be suffixed with an asterisk (`*`) to denote a zero or more parameter match.
The prefixed path delimiter is also taken into account for the match.

```php
use MNC\PathToRegExpPHP\PathRegExpFactory;

$pathRegex = PathRegExpFactory::create('/:foo*');

$result = $pathRegex->match('/');
$result->getMatchedString();    // '/'
$result->getValues();           // ['foo' => null];

$result = $pathRegex->match('/bar/baz');
$result->getMatchedString();    // '/bar/baz'
$result->getValues();           // ['foo' => 'bar/baz']
```

##### One or more

Parameters can be suffixed with a plus sign (`+`) to denote a one or more parameters match.
The prefixed path delimiter is included in the match.

```php
use MNC\PathToRegExpPHP\PathRegExpFactory;

$pathRegex = PathRegExpFactory::create('/:foo+');

$pathRegex->match('/'); // Will throw NoMatchException

$result = $pathRegex->match('/bar/baz');
$result->getMatchedString();    // '/bar/baz'
$result->getValues();           // ['foo' => 'bar/baz']
```

#### Custom Match Parameters

All parameters can be provided a custom matching regexp and override the default.

> Please note: Backslashes need to be escaped in strings.

```php
use MNC\PathToRegExpPHP\PathRegExpFactory;

$pathRegex = PathRegExpFactory::create('/:foo(\\d+)');

$result = $pathRegex->match('/123');
$result->getMatchedString();    // '/123'
$result->getValues();           // ['foo' => '123']

$pathRegex->match('/abc'); // Will throw a NoMatchException
```

#### Unnamed Parameters

It is possible to write an unnamed parameter that is only a matching group.
It works the same as a named parameter, except it will be numerically indexed.

```php
use MNC\PathToRegExpPHP\PathRegExpFactory;

$pathRegex = PathRegExpFactory::create('/:foo/(.*)');

$result = $pathRegex->match('/test/route');
$result->getMatchedString();   // '/test/route'
$result->getValues();          // ['foo' => 'test', '0' => 'route']
```