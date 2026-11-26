<?php

declare(strict_types=1);

namespace Dreamteam\Test\Request;

use Bitrix\Main\Validation\Rule\Length;
use Bitrix\Main\Validation\Rule\NotEmpty;

final readonly class AddNewRequest
{
    public function __construct(
        #[NotEmpty(allowZero: true)]
        #[Length(max: 255)]
        public string $name,
        #[NotEmpty(allowZero: true)]
        #[Length(max: 255)]
        public string $code,
        #[NotEmpty(allowZero: true)]
        public string $description,
    ) {
    }
}
