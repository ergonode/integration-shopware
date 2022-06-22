<?php

declare(strict_types=1);

namespace Strix\Ergonode\Manager;

use Shopware\Core\Framework\Context;
use Strix\Ergonode\Persistor\LanguagePersistor;
use Strix\Ergonode\Provider\ErgonodeLanguageProvider;

class LanguageManager
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

    public function syncLanguages(Context $context): array
    {
        $entities = [];

        $generator = $this->ergonodeLanguageProvider->provideActiveLanguages();

        foreach ($generator as $languages) {
            $entities = array_merge_recursive(
                $entities,
                $this->languagePersistor->persistStream($languages, $context)
            );
        }

        return $entities;
    }
}