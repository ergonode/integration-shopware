<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Api;

use Ergonode\IntegrationShopware\Util\IsoCodeConverter;

class LanguageListStreamResultsProxy extends AbstractStreamResultsProxy
{
    public const MAIN_FIELD = 'languageList';

    public function getIsoCodes(bool $inShopwareFormat = true): array
    {
        $isoCodes = [];

        foreach ($this->getEdges() as $edge) {
            if (!empty($edge['locale'])) {
                $isoCodes[] = $edge['locale'];
            }
        }

        return $inShopwareFormat ? IsoCodeConverter::ergonodeToShopwareIso($isoCodes) : $isoCodes;
    }
}