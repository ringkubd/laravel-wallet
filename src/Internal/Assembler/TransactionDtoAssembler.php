<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Service\ClockServiceInterface;
use Bavix\Wallet\Internal\Service\UuidFactoryServiceInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class TransactionDtoAssembler implements TransactionDtoAssemblerInterface
{
    public function __construct(
        private UuidFactoryServiceInterface $uuidService,
        private ClockServiceInterface $clockService,
    ) {
    }

    public function create(
        Model $payable,
        int $walletId,
        string $type,
        float|int|string $amount,
        float|int|string $fee,
        bool $confirmed,
        ?array $meta,
        ?string $uuid
    ): TransactionDtoInterface {
        return new TransactionDto(
            $uuid ?? $this->uuidService->uuid4(),
            $payable->getMorphClass(),
            $payable->getKey(),
            $walletId,
            $type,
            $amount,
            $fee,
            $confirmed,
            $meta,
            $this->clockService->now(),
            $this->clockService->now(),
        );
    }
}
