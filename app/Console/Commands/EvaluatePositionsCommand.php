<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Container;
use App\Services\Forex\PositionMonitorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'forex:evaluate-positions', description: 'Check all open positions for stop-loss, take-profit, or margin-call liquidation.')]
final class EvaluatePositionsCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $service = (new Container())->make(PositionMonitorService::class);
        $closed = $service->evaluateAll();

        $output->writeln("Closed {$closed} position(s).");

        return Command::SUCCESS;
    }
}
