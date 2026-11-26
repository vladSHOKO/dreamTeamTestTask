<?php

declare(strict_types=1);

namespace Dreamteam\Test\Request;

use Bitrix\Main\Type\DateTime;

final readonly class ListNewsRequest
{
    public function __construct(
        public ?DateTime $dateFrom,
        public ?DateTime $dateToExclusive,
        public ?string $name,
    ) {
    }
}
