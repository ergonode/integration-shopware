<?php

declare(strict_types=1);

namespace Strix\Ergonode\Command;

use GraphQL\Query;
use Strix\Ergonode\Api\Client\ErgonodeGqlClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GqlQueryCommand extends Command
{
    protected static $defaultName = 'strix:ergonode:gql';

    private ErgonodeGqlClient $gqlClient;

    public function __construct(ErgonodeGqlClient $gqlClient)
    {
        parent::__construct();

        $this->gqlClient = $gqlClient;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $query = new Query('productStream');
        $query->setSelectionSet([
            'totalCount',
        ]);

        $result = $this->gqlClient->query($query);

        if (null === $result) {
            $io->error('Request failed');

            return self::FAILURE;
        }

        $io->success(sprintf("Request successful: %s \nResponse below", $result->getStatusCode()));

        dump($result->getBody()->getContents());

        return self::SUCCESS;
    }
}