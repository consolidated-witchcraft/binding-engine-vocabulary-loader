<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\Exceptions;

final class InvalidJsonException extends AbstractVocabularyLoadingException
{
    private const string MESSAGE_PATTERN = 'Invalid JSON: %s';

    public function __construct(string $detail, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            message: sprintf(self::MESSAGE_PATTERN, $detail),
            code: $code,
            previous: $previous,
        );
    }
}
