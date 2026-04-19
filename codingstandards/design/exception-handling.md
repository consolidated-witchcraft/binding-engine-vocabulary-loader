## Exception Handling
* All exceptions MUST be handled at an appropriate architectural boundary and MUST NOT be left unmanaged
* Exceptions MUST be specific; don't catch or throw generic exceptions
* Exceptions within a service directory MUST extend a base exception within that service
* Exceptions from external libraries or PHP runtime SHOULD be allowed to bubble to the application layer unless they can be meaningfully converted into domain-specific exceptions.
* If you catch an exception and convert it into a domain-specific exception, you MUST pass the originating exception as $previous and preserve this chaining throughout the call stack.
* You MUST NOT catch generic exceptions or throwables such as \Exception, \RuntimeException, or \Throwable anywhere but at the application layer, where you may convert them into appropriate user feedback.
    * All such catches MUST be logged.
* If an exception has a message, that message's text MUST be set via a private const string called MESSAGE_PATTERN. The message itself is constructed using this pattern and sprintf for any necessary substitutions. For example:
```php
    class FooException extends \AbstractException {

        private const string MESSAGE_PATTERN = "Attempted to provide a value of type '%s' to Foo. Foo only accepts Bar";

        public function __construct(string $valueType, int $code = 0, ?\Throwable $previous = null) {

            parent::__construct(sprintf(self::MESSAGE_PATTERN, $valueType), $code, $previous);
        }
    }
```
* Exceptions MUST NOT have unit tests.
