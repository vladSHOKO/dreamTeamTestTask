<?php

declare(strict_types=1);

namespace Dreamteam\Test\Repository;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\Type\DateTime;
use InvalidArgumentException;
use RuntimeException;

final class NewsRepository
{
    private const FIELDS = ['ID', 'NAME', 'CODE', 'PREVIEW_TEXT', 'DATE_CREATE'];

    public function findById(int $id): ?array
    {
        $dataClass = $this->getElementDataClass();
        $news = $dataClass::query()
            ->setSelect(self::FIELDS)
            ->where('ID', $id)
            ->fetch();

        return $news ?: null;
    }

    public function getList(
        ?DateTime $dateFrom = null,
        ?DateTime $dateToExclusive = null,
        ?string $name = null,
        int $limit = 100,
    ): array {
        if ($limit <= 0) {
            throw new InvalidArgumentException((string) Loc::getMessage('DREAMTEAM_TEST_NEWS_INVALID_LIMIT'));
        }

        $dataClass = $this->getElementDataClass();
        $query = $dataClass::query()
            ->setSelect(self::FIELDS)
            ->setOrder(['DATE_CREATE' => 'DESC', 'ID' => 'DESC'])
            ->setLimit($limit);

        if ($dateFrom !== null) {
            $query->where('DATE_CREATE', '>=', $dateFrom);
        }
        if ($dateToExclusive !== null) {
            $query->where('DATE_CREATE', '<', $dateToExclusive);
        }
        if ($name !== null && $name !== '') {
            $query->whereLike('NAME', '%' . addcslashes($name, '\\%_') . '%');
        }

        return $query->fetchAll();
    }

    public function add(string $name, string $code, string $description): AddResult
    {
        $dataClass = $this->getElementDataClass();

        return $dataClass::add([
            'NAME' => $name,
            'CODE' => $code,
            'PREVIEW_TEXT' => $description,
        ]);
    }

    private function getElementDataClass(): string
    {
        Loader::requireModule('iblock');

        $iblock = IblockTable::query()
            ->setSelect(['ID', 'API_CODE'])
            ->where('IBLOCK_TYPE_ID', 'content')
            ->where('CODE', 'news')
            ->setLimit(1)
            ->fetchObject();

        if (!$iblock) {
            throw new RuntimeException((string) Loc::getMessage('DREAMTEAM_TEST_NEWS_IBLOCK_NOT_FOUND'));
        }

        $entity = IblockTable::compileEntity($iblock);
        if (!$entity) {
            throw new RuntimeException((string) Loc::getMessage('DREAMTEAM_TEST_NEWS_IBLOCK_API_CODE_REQUIRED'));
        }

        return $entity->getDataClass();
    }
}
