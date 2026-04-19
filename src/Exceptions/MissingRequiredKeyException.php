<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions;

final class MissingRequiredKeyException extends AbstractVocabularyLoadingException
{
    private const string ROOT_PATH = '';
    private const string ROOT_MESSAGE_PATTERN = 'Missing required key "%s".';
    private const string NESTED_MESSAGE_PATTERN = 'Missing required key "%s" at "%s".';

    public function __construct(string $key, string $path, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            message: $path === self::ROOT_PATH
                ? sprintf(self::ROOT_MESSAGE_PATTERN, $key)
                : sprintf(self::NESTED_MESSAGE_PATTERN, $key, $path),
            code: $code,
            previous: $previous,
        );
    }
}
