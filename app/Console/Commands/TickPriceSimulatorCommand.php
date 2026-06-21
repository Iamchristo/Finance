<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Container;
use App\Services\Forex\PriceSimulatorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'forex:tick-prices', description: 'Advance the simulated price feed by one tick for all active instruments.')]
final class TickPriceSimulatorCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $service = (new Container())->make(PriceSimulatorService::class);
        $ticked = $service->tick();

        $output->writeln("Ticked {$ticked} instrument(s).");

        return Command::SUCCESS;
    }
}
