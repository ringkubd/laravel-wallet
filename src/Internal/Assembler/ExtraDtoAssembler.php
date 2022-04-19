<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Dto\ExtraDto;
use Bavix\Wallet\Internal\Dto\ExtraDtoInterface;

final class ExtraDtoAssembler implements ExtraDtoAssemblerInterface
{
    public function __construct(private OptionDtoAssemblerInterface $optionDtoAssembler)
    {
    }

    public function create(ExtraDtoInterface|array|null $data): ExtraDtoInterface
    {
        if ($data instanceof ExtraDtoInterface) {
            return $data;
        }

        $option = $this->optionDtoAssembler->create($data);

        return new ExtraDto($option, $option);
    }
}