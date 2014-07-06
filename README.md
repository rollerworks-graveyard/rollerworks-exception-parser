RollerworksExceptionParser
==========================

[![Build Status](https://secure.travis-ci.org/rollerworks/rollerworks-exception-parser.png?branch=master)](http://travis-ci.org/rollerworks/rollerworks-exception-parser)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/rollerworks/rollerworks-exception-parser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/rollerworks/rollerworks-exception-parser/?branch=master)

This package provides the Rollerworks ExceptionParser
which extracts exception-data to an array.

Basically it wraps multiple exception-catch blocks into one call,
and returns the parsed information.

## Installation

### 1. Using Composer

To install the Rollerworks ExceptionParser, add `rollerworks/exception-parser` to your composer.json

```bash
$ php composer.phar require rollerworks/exception-parser:"~1.0"
```

Or manually, by adding the following to your
`composer.json` file:

```js
// composer.json
{
    // ...
    require: {
        // ...
        "rollerworks/exception-parser": "~1.0"
    }
}
```

Then, you can install the new dependency by running Composer's `update`
command from the directory where your `composer.json` file is located:

```bash
$ php composer update exception-parser
```

**Note:** This package is stand-alone, composer is only used for
generating the autoloader class.

You can also use your own autoloader, but make sure its [PSR-4][0] compatible.
See the composer.json file in this package for mapping information.

## Usage

Usage is very simple, each exception that you want to process needs
a compatible parser which parses the Exception to an array.

The ExceptionParserManager passes trough all the registered exception-parses
in order of registration, till one accepts the exception.

When no compatible parser is found an empty array is returned instead.

### Exception class

```php

class FieldRequiredException extends \RuntimeException
{
    private $fieldName;
    private $groupIdx;
    private $nestingLevel;

    /**
     * @param string  $fieldName
     * @param integer $groupIdx
     * @param integer $nestingLevel
     */
    public function __construct($fieldName, $groupIdx, $nestingLevel)
    {
        $this->fieldName = $fieldName;
        $this->groupIdx = $groupIdx;
        $this->nestingLevel = $nestingLevel;

        parent::__construct(sprintf('Field "%s" is required but is missing in group %d at nesting level %d.', $fieldName, $groupIdx, $nestingLevel));
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return int
     */
    public function getGroupIdx()
    {
        return $this->groupIdx;
    }

    /**
     * @return int
     */
    public function getNestingLevel()
    {
        return $this->nestingLevel;
    }
}
```

### Exception Parser

The `SearchFieldRequiredExceptionParser` exception parser
parses the `FieldRequiredException` shown above.

```php

use Acme\Exception\FieldRequiredException;
use Rollerworks\Component\ExceptionParser\ExceptionParserInterface;

class SearchFieldRequiredExceptionParser implements ExceptionParserInterface
{
    /**
     * Returns whether the processor accepts the exception.
     *
     * @param \Exception $exception
     *
     * @return bool
     */
    public function accepts(\Exception $exception)
    {
        return $exception instanceof FieldRequiredException;
    }

    /**
     * Returns parameters parsed from the exception.
     *
     * @param \Exception $exception
     *
     * @return array
     */
    public function parseException(\Exception $exception)
    {
        /** @var FieldRequiredException $exception */

        return array(
            'message' => 'Field "{{ field }}" is required but is missing in group {{ group }} at nesting level {{ nesting }}.',
            'field' => $exception->getFieldName(),
            'group' => $exception->getGroupIdx(),
            'nesting' => $exception->getNestingLevel(),
        );
    }
}
```

### ParserManager

Now register the Parser at the ExceptionParserManager.

**Note:** You can register as many processors as needed.

```php

use Rollerworks\Component\ExceptionParser\ExceptionParserManager;

require 'vendor/autoload.php';

$exceptionParser = new ExceptionParserManager();
$exceptionParser->addExceptionParser(new SearchFieldRequiredExceptionParser());

try {
    throw new Acme\Exception\FieldRequiredException('name', 0, 0);
} catch (Exception $exception) {
    $params = $exceptionParser->processException($exception);

    /*
    $params = array(
        'message' => 'Field "{{ field }}" is required but is missing in group {{ group }} at nesting level {{ nesting }}.',
        'field' => 'name',
        'group' => 0,
        'nesting' => 0,
    )
    */
}
```

#### Key transforming

The ExceptionParserManager also allows to transform the array-key
to another format like '{{ name }}'.

'{var}' is the placeholder which is replaced by the current key.

```php
$exceptionParser = new ExceptionParserManager('{{ {var} }}');
```

Or you can use a callback that returns the transformed key.
This is very useful if the keys contains a prefix or is wrapped inside a special format.

```php
$keyTransformer = new function ($value) {
    return ltrim($value, '$');
};

$exceptionParser = new ExceptionParserManager($keyTransformer);
```

**Note:** If the callback returns null or void the key is added as incrementing index.
**Only when the "callback" returns null** or void, setting null as constructor value
is the default and will not transform the array-keys.

```php
require 'vendor/autoload.php';

$keyTransformer = new function ($value) {
    return null;
};

$exceptionParser = new ExceptionParserManager($keyTransformer);
$exceptionParser->addExceptionParser(new ExceptionParser\SearchFieldRequiredExceptionParser());

try {
    throw new Acme\Exception\FieldRequiredException('name', 0, 0);
} catch (Exception $exception) {
    $params = $exceptionParser->processException($exception);

    /*
    $params = array(
        0 => 'Field "{{ field }}" is required but is missing in group {{ group }} at nesting level {{ nesting }}.',
        1 => 'name',
        2 => 0,
        3 => 0,
    )
    */
}

```

[0]: http://www.php-fig.org/psr/psr-4/
