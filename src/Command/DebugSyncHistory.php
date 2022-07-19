<?php

declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Command;

use Ergonode\IntegrationShopware\Service\History\SyncHistoryLogger;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DebugSyncHistory extends Command
{
    protected static $defaultName = 'ergonode:debug:history';

    private Context $context;

    private SyncHistoryLogger $syncHistoryService;

    private LoggerInterface $syncLogger;

    public function __construct(
        SyncHistoryLogger $syncHistoryService,
        LoggerInterface $syncLogger
    ) {
        parent::__construct();

        $this->context = new Context(new SystemSource());
        $this->syncHistoryService = $syncHistoryService;
        $this->syncLogger = $syncLogger;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $id = $this->syncHistoryService->start('AwesomeSync', $this->context);

        // simulate some errors/warnings
        $this->syncLogger->warning('some warning occured');
        $this->syncLogger->warning('second warning occured');
        $this->syncLogger->error('and now there was an error :(');

        // simulate some info
        $this->syncLogger->info('but now suddenly we got some additional info :)');
        $this->syncLogger->info('and another one');

        $this->syncHistoryService->finish($id, $this->context, 91);

        $io->info(['Sync history ID', $id]);

        return self::SUCCESS;
    }
}
