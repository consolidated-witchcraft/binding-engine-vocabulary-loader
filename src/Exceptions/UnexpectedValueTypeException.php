<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions;

final class UnexpectedValueTypeException extends AbstractVocabularyLoadingException
{
    private const string MESSAGE_PATTERN = 'Expected %s at "%s".';

    public function __construct(string $expectedType, string $path, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            message: sprintf(self::MESSAGE_PATTERN, $expectedType, $path),
            code: $code,
            previous: $previous,
        );
    }
}
