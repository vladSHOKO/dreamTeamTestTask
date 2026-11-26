<?php

declare(strict_types=1);

namespace Dreamteam\Test\Request;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use InvalidArgumentException;

final class NewsRequestParser
{
    public static function parseAdd(array $data): AddNewRequest
    {
        return new AddNewRequest(
            name: trim(self::readText($data['name'] ?? null)),
            code: trim(self::readText($data['code'] ?? null)),
            description: self::readText($data['description'] ?? null),
        );
    }

    public static function parseList(?string $dateFrom, ?string $dateTo, ?string $name): ListNewsRequest
    {
        $from = self::parseDate($dateFrom, 'date_from');
        $to = self::parseDate($dateTo, 'date_to');
        if ($from !== null && $to !== null && $from->getTimestamp() > $to->getTimestamp()) {
            throw new InvalidArgumentException((string) Loc::getMessage('DREAMTEAM_TEST_NEWS_INVALID_DATE_RANGE'));
        }

        return new ListNewsRequest($from, $to?->add('1 day'), $name);
    }

    private static function readText(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    private static function parseDate(?string $value, string $field): ?DateTime
    {
        if ($value === null) {
            return null;
        }

        $date = CheckDateTime($value, 'YYYY-MM-DD')
            ? DateTime::tryParse($value, '!Y-m-d')
            : null;

        if ($date === null) {
            throw new InvalidArgumentException((string) Loc::getMessage(
                'DREAMTEAM_TEST_NEWS_INVALID_DATE',
                ['#FIELD#' => $field],
            ));
        }

        return $date;
    }
}
