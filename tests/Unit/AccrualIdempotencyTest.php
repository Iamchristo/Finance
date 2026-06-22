<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Database;
use App\Repositories\InvestmentRepository;
use App\Repositories\RealEstateRepository;
use App\Services\Investment\AccrualService;
use App\Services\RealEstate\RealEstateAccrualService;
use App\Services\WalletService;
use DateTimeImmutable;
use Tests\Support\DatabaseTestCase;

/**
 * Re-running a day's accrual cron must not double-credit a subscription or
 * property investment. The UNIQUE(subscription_id, accrual_date) /
 * UNIQUE(property_investment_id, accrual_date) constraints exist for exactly
 * this, but the guarantee only holds if the credit itself is gated on the
 * same check — these tests pin that behavior down.
 */
final class AccrualIdempotencyTest extends DatabaseTestCase
{
    public function testInvestmentAccrualIsNotAppliedTwiceForTheSameDay(): void
    {
        $userId = $this->createTestUser();
        $investments = new InvestmentRepository();
        $wallet = new WalletService();
        $service = new AccrualService($investments, $wallet);

        $plan = $this->firstActivePlan($investments);
        $subscriptionId = $investments->createSubscription($userId, (int) $plan['id'], '1000.00000000', 30);

        $firstRunApplied = $service->runDailyAccrual();
        self::assertGreaterThanOrEqual(1, $firstRunApplied, 'The new subscription must be among those accrued.');

        $balanceAfterFirstRun = $wallet->getBalances($userId)['investment'];

        $secondRunApplied = $service->runDailyAccrual();
        self::assertSame(0, $secondRunApplied, 'Re-running the same day must not apply a second accrual.');

        $balanceAfterSecondRun = $wallet->getBalances($userId)['investment'];
        self::assertSame($balanceAfterFirstRun, $balanceAfterSecondRun, 'Balance must be unchanged by the duplicate run.');

        $logCount = $this->countAccrualLogs('investment_accrual_logs', 'subscription_id', $subscriptionId);
        self::assertSame(1, $logCount, 'Exactly one accrual log row must exist for the day.');
    }

    public function testRealEstateAccrualIsNotAppliedTwiceForTheSameDay(): void
    {
        $userId = $this->createTestUser();
        $realEstate = new RealEstateRepository();
        $wallet = new WalletService();
        $service = new RealEstateAccrualService($realEstate, $wallet);

        $property = $this->firstActiveProperty($realEstate);
        $investmentId = $realEstate->createInvestment($userId, (int) $property['id'], 10, '5000.00000000');

        $firstRunApplied = $service->runDailyAccrual();
        self::assertGreaterThanOrEqual(1, $firstRunApplied, 'The new investment must be among those accrued.');

        $balanceAfterFirstRun = $wallet->getBalances($userId)['realestate'];

        $secondRunApplied = $service->runDailyAccrual();
        self::assertSame(0, $secondRunApplied, 'Re-running the same day must not apply a second accrual.');

        $balanceAfterSecondRun = $wallet->getBalances($userId)['realestate'];
        self::assertSame($balanceAfterFirstRun, $balanceAfterSecondRun, 'Balance must be unchanged by the duplicate run.');

        $logCount = $this->countAccrualLogs('re_accrual_logs', 'property_investment_id', $investmentId);
        self::assertSame(1, $logCount, 'Exactly one accrual log row must exist for the day.');
    }

    public function testInvestmentAccrualHasAccrualForDateReflectsCommittedState(): void
    {
        $userId = $this->createTestUser();
        $investments = new InvestmentRepository();
        $wallet = new WalletService();
        $service = new AccrualService($investments, $wallet);

        $plan = $this->firstActivePlan($investments);
        $subscriptionId = $investments->createSubscription($userId, (int) $plan['id'], '1000.00000000', 30);

        self::assertFalse($investments->hasAccrualForDate($subscriptionId, (new DateTimeImmutable())->format('Y-m-d')));

        $service->runDailyAccrual();

        self::assertTrue($investments->hasAccrualForDate($subscriptionId, (new DateTimeImmutable())->format('Y-m-d')));
    }

    /** @return array<string, mixed> */
    private function firstActivePlan(InvestmentRepository $investments): array
    {
        $plans = $investments->activePlans();
        self::assertNotEmpty($plans, 'Fixture data must include at least one active investment plan.');

        return $plans[0];
    }

    /** @return array<string, mixed> */
    private function firstActiveProperty(RealEstateRepository $realEstate): array
    {
        $properties = $realEstate->activeProperties();
        self::assertNotEmpty($properties, 'Fixture data must include at least one active property.');

        return $properties[0];
    }

    private function countAccrualLogs(string $table, string $column, int $id): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = :id");
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn();
    }
}
