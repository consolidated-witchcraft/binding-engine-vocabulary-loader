<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions;

final class MalformedTopLevelStructureException extends AbstractVocabularyLoadingException
{
    private const string MESSAGE_PATTERN = 'Expected top-level JSON object.';

    public function __construct(int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            message: self::MESSAGE_PATTERN,
            code: $code,
            previous: $previous,
        );
    }
}
