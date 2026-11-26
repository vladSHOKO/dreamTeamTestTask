<?php

declare(strict_types=1);

namespace Dreamteam\Test\Service;

use Bitrix\Main\Type\DateTime;
use Dreamteam\Test\Dto\NewsDto;
use Dreamteam\Test\Repository\NewsRepository;
use RuntimeException;

final readonly class NewsService
{
    public function __construct(
        private NewsRepository $newsRepository,
    ){}

    public function add(string $name, string $code, string $description): ?int
    {
        $result = $this->newsRepository->add($name, $code, $description);

        if (!$result->isSuccess()) {
            throw new RuntimeException(implode('; ', $result->getErrorMessages()));
        }

        return $result->getId();
    }

    /**
     * @return array<int, NewsDto>
     */
    public function getList(
        ?DateTime $dateFrom = null,
        ?DateTime $dateToExclusive = null,
        ?string $name = null,
    ): array {
        return $this->newsRepository->getList($dateFrom, $dateToExclusive, $name);
    }

    public function findById(int $id): ?NewsDto
    {
        return $this->newsRepository->findById($id);
    }
}
