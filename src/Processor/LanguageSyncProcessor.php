<?php

declare(strict_types=1);

namespace Strix\Ergonode\Processor;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\Persistor\LanguagePersistor;
use Strix\Ergonode\Provider\ErgonodeLanguageProvider;

class LanguageSyncProcessor
{
    private ErgonodeLanguageProvider $ergonodeLanguageProvider;

    private LanguagePersistor $languagePersistor;

    public function __construct(
        ErgonodeLanguageProvider $ergonodeLanguageProvider,
        LanguagePersistor $languagePersistor
    ) {
        $this->ergonodeLanguageProvider = $ergonodeLanguageProvider;
        $this->languagePersistor = $languagePersistor;
    }

    public function process(Context $context): array
    {
        $generator = $this->ergonodeLanguageProvider->provideActiveLanguages();
        $entities = [];

        foreach ($generator as $languages) {
            $entities = array_merge_recursive(
                $entities,
                $this->languagePersistor->persistStream($languages, $context)
            );
        }

        return $entities;
    }
}