<?php

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\TypeTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class dreamteam_test extends CModule
{
    private const IBLOCK_TYPE = 'content';
    private const IBLOCK_CODE = 'news';

    public function __construct()
    {
        require __DIR__ . '/version.php';

        $this->MODULE_ID = 'dreamteam.test';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('DREAMTEAM_TEST_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('DREAMTEAM_TEST_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = 'Vladislav Kuzminov';
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if (!Loader::includeModule('iblock')) {
            $APPLICATION->ThrowException(Loc::getMessage('DREAMTEAM_TEST_ERROR_IBLOCK'));
            return false;
        }

        if ($this->createIblockIfNotExist() === null) {
            return false;
        }

        ModuleManager::registerModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        if (!Loader::includeModule('iblock')) {
            $APPLICATION->ThrowException(Loc::getMessage('DREAMTEAM_TEST_ERROR_IBLOCK'));
            return false;
        }

        $iblock = IblockTable::query()
            ->setSelect(['ID'])
            ->where('IBLOCK_TYPE_ID', self::IBLOCK_TYPE)
            ->where('CODE', self::IBLOCK_CODE)
            ->setLimit(1)
            ->fetch();

        if ($iblock && !CIBlock::Delete((int) $iblock['ID'])) {
            if (!$APPLICATION->GetException()) {
                $APPLICATION->ThrowException(Loc::getMessage('DREAMTEAM_TEST_ERROR_DELETE_IBLOCK'));
            }
            return false;
        }

        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    private function createIblockIfNotExist(): ?int
    {
        global $APPLICATION;

        $existingIblock = IblockTable::query()
            ->setSelect(['ID'])
            ->where('IBLOCK_TYPE_ID', self::IBLOCK_TYPE)
            ->where('CODE', self::IBLOCK_CODE)
            ->setLimit(1)
            ->fetch();
        if ($existingIblock) {
            return (int) $existingIblock['ID'];
        }

        $siteId = CSite::GetDefSite();
        if (!$siteId) {
            $APPLICATION->ThrowException(Loc::getMessage('DREAMTEAM_TEST_ERROR_SITE'));
            return null;
        }

        if (!TypeTable::getByPrimary(self::IBLOCK_TYPE)->fetch()) {
            $type = new CIBlockType();
            if (!$type->Add([
                'ID' => self::IBLOCK_TYPE,
                'LANG' => [
                    'ru' => ['NAME' => Loc::getMessage('DREAMTEAM_IBLOCK_TYPE_NAME')],
                    'en' => ['NAME' => 'Content'],
                ],
            ])) {
                $APPLICATION->ThrowException($type->LAST_ERROR);
                return null;
            }
        }

        $iblock = new CIBlock();
        $iblockId = $iblock->Add([
            'IBLOCK_TYPE_ID' => self::IBLOCK_TYPE,
            'CODE' => self::IBLOCK_CODE,
            'NAME' => Loc::getMessage('DREAMTEAM_TEST_IBLOCK_NAME'),
            'LID' => [$siteId],
        ]);
        if (!$iblockId) {
            $APPLICATION->ThrowException($iblock->LAST_ERROR);
            return null;
        }

        return (int) $iblockId;
    }
}
