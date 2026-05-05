<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\Exceptions;

final class InvalidEnumValueException extends AbstractVocabularyLoadingException
{
    private const string MESSAGE_PATTERN = 'Invalid %s "%s" at "%s".';

    public function __construct(string $fieldName, string $value, string $path, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            message: sprintf(self::MESSAGE_PATTERN, $fieldName, $value, $path),
            code: $code,
            previous: $previous,
        );
    }
}
