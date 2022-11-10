<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Util;

class ChecksumContainer
{
    private array $md5Container = [];

    public function push(string $prefix, string $suffix): void
    {
        $checksum = $this->generate($prefix, $suffix);

        $this->md5Container[$checksum] = $checksum;
    }

    public function exists(string $prefix, string $suffix): bool
    {
        return in_array($this->generate($prefix, $suffix), $this->md5Container);
    }

    public function clear(): void
    {
        $this->md5Container = [];
    }

    private function generate(string $prefix, string $suffix): string
    {
        return md5(
            sprintf('%s_%s', $prefix, $suffix)
        );
    }
}