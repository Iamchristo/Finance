<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Container;
use App\Services\RealEstate\RealEstateAccrualService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'realestate:accrue', description: 'Apply daily rental/ROI accrual to active fractional real-estate investments.')]
final class RunRealEstateAccrualCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $service = (new Container())->make(RealEstateAccrualService::class);
        $applied = $service->runDailyAccrual();

        $output->writeln("Applied {$applied} real-estate accrual(s).");

        return Command::SUCCESS;
    }
}
