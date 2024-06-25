<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransactionCreatedEventAssemblerInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Service\DispatcherServiceInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;

/**
 * @internal
 */
final readonly class TransactionService implements TransactionServiceInterface
{
    public function __construct(
        private TransactionCreatedEventAssemblerInterface $transactionCreatedEventAssembler,
        private DispatcherServiceInterface $dispatcherService,
        private AssistantServiceInterface $assistantService,
        private RegulatorServiceInterface $regulatorService,
        private PrepareServiceInterface $prepareService,
        private CastServiceInterface $castService,
        private AtmServiceInterface $atmService,
    ) {
    }

    /**
     * @throws RecordNotFoundException
     */
    public function makeOne(
        Wallet $wallet,
        string $type,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true
    ): Transaction {
        assert(in_array($type, [Transaction::TYPE_DEPOSIT, Transaction::TYPE_WITHDRAW], true));

        $taxService = app(TaxServiceInterface::class);

        $fee = $type === Transaction::TYPE_DEPOSIT
            ? $taxService->getDepositFee($wallet, $amount)
            : $taxService->getWithdrawFee($wallet, $amount);
        $mathService = app(MathServiceInterface::class);

        $amount = $mathService->sub($amount, $fee, $wallet->decimal_places);

        $dto = $type === Transaction::TYPE_DEPOSIT
            ? $this->prepareService->deposit($wallet, (string) $amount, $fee, $meta, $confirmed, Transaction::TYPE_DEPOSIT)
            : $this->prepareService->withdraw($wallet, (string) $amount, $fee, $meta, $confirmed, Transaction::TYPE_WITHDRAW);

        $transactions = $this->apply([
            $dto->getWalletId() => $wallet,
        ], [$dto]);

        return current($transactions);
    }

    /**
     * @param non-empty-array<int, Wallet> $wallets
     * @param non-empty-array<int, TransactionDtoInterface> $objects
     * @return non-empty-array<string, Transaction>
     *
     * @throws RecordNotFoundException
     */
    public function apply(array $wallets, array $objects): array
    {
        $transactions = $this->atmService->makeTransactions($objects); // q1
        $totals = $this->assistantService->getSums($objects);
        assert(count($objects) === count($transactions));

        foreach ($totals as $walletId => $total) {
            $wallet = $wallets[$walletId] ?? null;
            assert($wallet instanceof Wallet);

            $object = $this->castService->getWallet($wallet);
            assert($object->getKey() === $walletId);

            $this->regulatorService->increase($object, $total);
        }

        foreach ($transactions as $transaction) {
            $this->dispatcherService->dispatch($this->transactionCreatedEventAssembler->create($transaction));
        }

        return $transactions;
    }
}
