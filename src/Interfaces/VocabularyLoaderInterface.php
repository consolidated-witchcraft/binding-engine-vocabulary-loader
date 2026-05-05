<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\Interfaces;

use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Vocabulary;

interface VocabularyLoaderInterface
{
    public function load(string $input): Vocabulary;
}
