<?php

declare(strict_types=1);

namespace Strix\Ergonode\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use Strix\Ergonode\Util\IsoCodeConverter;

class IsoCodeConverterTest extends TestCase
{
    /**
     * @dataProvider isoDataProvider
     */
    public function testErgonodeToShopwareIsoMethod(string $input, string $expectedOutput)
    {
        $output = IsoCodeConverter::ergonodeToShopwareIso($input);

        $this->assertSame($output, $expectedOutput);
    }

    public function isoDataProvider(): array
    {
        return [
            ['', ''],
            ['pl_PL', 'pl-PL'],
            ['en_US', 'en-US'],
            ['de_DE', 'de-DE']
        ];
    }
}
