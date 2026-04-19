<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\VocabularyLoader\Interfaces;

use ConundrumCodex\BindingEngine\Vocabulary\Vocabulary;

interface VocabularyLoaderInterface
{
    public function load(string $input): Vocabulary;
}
