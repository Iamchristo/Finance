<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Container;
use App\Services\Investment\AccrualService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'investment:accrue', description: 'Apply daily ROI accrual to active investment subscriptions.')]
final class RunInvestmentAccrualCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $service = (new Container())->make(AccrualService::class);
        $applied = $service->runDailyAccrual();

        $output->writeln("Applied {$applied} investment accrual(s).");

        return Command::SUCCESS;
    }
}
