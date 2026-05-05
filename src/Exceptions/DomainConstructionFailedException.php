<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\Exceptions;

final class DomainConstructionFailedException extends AbstractVocabularyLoadingException
{
    private const string ROOT_MESSAGE_PATTERN = 'Invalid %s: %s';
    private const string NESTED_MESSAGE_PATTERN = 'Invalid %s at "%s": %s';

    public function __construct(string $subject, string $detail, ?string $path = null, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            message: $path === null
                ? sprintf(self::ROOT_MESSAGE_PATTERN, $subject, $detail)
                : sprintf(self::NESTED_MESSAGE_PATTERN, $subject, $path, $detail),
            code: $code,
            previous: $previous,
        );
    }
}
