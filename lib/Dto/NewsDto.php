<?php

declare(strict_types=1);

namespace Dreamteam\Test\Dto;

use Bitrix\Main\Type\DateTime;

class NewsDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public ?DateTime $dateCreate,
    ){}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'date_create' => $this->dateCreate?->format(DATE_ATOM),
        ];
    }
}
