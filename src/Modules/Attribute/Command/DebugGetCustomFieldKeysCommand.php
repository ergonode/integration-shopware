<?php

declare(strict_types=1);

namespace Strix\Ergonode\Modules\Attribute\Command;

use Strix\Ergonode\Provider\ConfigProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugGetCustomFieldKeysCommand extends Command
{
    protected static $defaultName = 'strix:ergonode:debug:fields';

    private ConfigProvider $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        dump($this->configProvider->getErgonodeCustomFields());

        return self::SUCCESS;
    }
}
