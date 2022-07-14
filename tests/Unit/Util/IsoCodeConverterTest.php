<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Tests\Unit\Util;

use Ergonode\IntegrationShopware\Util\IsoCodeConverter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class IsoCodeConverterTest extends TestCase
{
    /**
     * @param array|string $input
     * @param array|string $expectedOutput
     * @dataProvider isoDataProvider
     */
    public function testErgonodeToShopwareIsoMethod($input, $expectedOutput)
    {
        $output = IsoCodeConverter::ergonodeToShopwareIso($input);

        $this->assertSame($output, $expectedOutput);
    }

    /**
     * @param array|string $input
     * @param array|string $expectedOutput
     * @dataProvider isoDataProvider
     */
    public function testShopwareToErgonodeIsoMethod($expectedOutput, $input)
    {
        $output = IsoCodeConverter::shopwareToErgonodeIso($input);

        $this->assertSame($output, $expectedOutput);
    }

    /**
     * @param mixed $input
     * @dataProvider wrongTypeIsoDataProvider
     */
    public function testIfErgonodeToShopwareIsoMethodWillThrowExceptionWhenParamOfWrongTypeProvided($input)
    {
        $this->expectException(InvalidArgumentException::class);

        IsoCodeConverter::ergonodeToShopwareIso($input);
    }

    public function isoDataProvider(): array
    {
        return [
            ['', ''],
            ['pl_PL', 'pl-PL'],
            ['en_US', 'en-US'],
            ['de_DE', 'de-DE'],
            [['de_DE'], ['de-DE']],
            [['de_DE', 'pl_PL'], ['de-DE', 'pl-PL']],
            [['__', '_', ''], ['--', '-', '']],
        ];
    }

    public function wrongTypeIsoDataProvider(): array
    {
        return [
            [null],
            [666],
            [21.37],
            [new Entity()],
        ];
    }
}
