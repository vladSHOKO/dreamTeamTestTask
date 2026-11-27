<?php

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\TypeLanguageTable;
use Bitrix\Iblock\TypeTable;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;


class dreamteam_test extends CModule
{
    private const IBLOCK_TYPE = 'content';
    private const IBLOCK_CODE = 'news';
    private const IBLOCK_API_CODE = 'DreamteamTestNews';
    private const ROUTES_FILE = 'dreamteam.test.php';

    public function __construct()
    {
        require __DIR__ . '/version.php';

        $this->MODULE_ID = 'dreamteam.test';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('DREAMTEAM_TEST_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('DREAMTEAM_TEST_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('DREAMTEAM_TEST_PARTNER_NAME');
    }

    public function doInstall()
    {
        global $APPLICATION;

        if (!Loader::includeModule('iblock')) {
            $APPLICATION->ThrowException(Loc::getMessage('DREAMTEAM_TEST_ERROR_IBLOCK'));
            return false;
        }

        if ($this->createIblockIfNotExist() === null) {
            return false;
        }

        if (!$this->installFiles()) {
            $APPLICATION->ThrowException(Loc::getMessage('DREAMTEAM_TEST_ERROR_INSTALL_ROUTES'));
            return false;
        }

        ModuleManager::registerModule($this->MODULE_ID);
    }

    public function doUninstall()
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

        if (!$this->uninstallFiles()) {
            $APPLICATION->ThrowException(Loc::getMessage('DREAMTEAM_TEST_ERROR_UNINSTALL_ROUTES'));
            return false;
        }

        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installFiles(): bool
    {
        if (!CopyDirFiles(
            __DIR__ . '/routes/' . self::ROUTES_FILE,
            Loader::getDocumentRoot() . '/local/routes/' . self::ROUTES_FILE
        )) {
            return false;
        }

        $this->setRouteEnabled(true);
        return true;
    }

    public function uninstallFiles(): bool
    {
        $this->setRouteEnabled(false);
        return File::deleteFile(Loader::getDocumentRoot() . '/local/routes/' . self::ROUTES_FILE);
    }

    private function setRouteEnabled(bool $enabled): void
    {
        $extraPath = Loader::getLocal('.settings_extra.php');
        if ($extraPath !== false && isset((require $extraPath)['routing'])) {
            return;
        }

        $settings = Configuration::getInstance();
        $routing = $settings->get('routing') ?? [];
        $files = $routing['config'] ?? [];
        if (in_array(self::ROUTES_FILE, $files, true) === $enabled) {
            return;
        }

        $routing['config'] = $enabled
            ? [...$files, self::ROUTES_FILE]
            : array_values(array_diff($files, [self::ROUTES_FILE]));

        $settingsPath = Loader::getLocal(Configuration::CONFIGURATION_FILE);
        if (!empty((require $settingsPath)['routing']['readonly'])) {
            $settings->addReadonly('routing', $routing);
        } else {
            $settings->add('routing', $routing);
        }
        $settings->saveConfiguration();
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

        if (!TypeTable::getByPrimary(self::IBLOCK_TYPE)->fetch() && !$this->createIblockType()) {
            return null;
        }

        $iblock = new CIBlock();
        $iblockId = $iblock->Add([
            'IBLOCK_TYPE_ID' => self::IBLOCK_TYPE,
            'CODE' => self::IBLOCK_CODE,
            'API_CODE' => self::IBLOCK_API_CODE,
            'NAME' => Loc::getMessage('DREAMTEAM_TEST_IBLOCK_NAME'),
            'LID' => [$siteId],
        ]);
        if (!$iblockId) {
            $APPLICATION->ThrowException($iblock->LAST_ERROR);
            return null;
        }

        return (int) $iblockId;
    }

    private function createIblockType(): bool
    {
        global $APPLICATION;

        $result = TypeTable::add(['ID' => self::IBLOCK_TYPE]);
        if ($result->isSuccess()) {
            $result = TypeLanguageTable::add([
                'IBLOCK_TYPE_ID' => self::IBLOCK_TYPE,
                'LANGUAGE_ID' => 'ru',
                'NAME' => Loc::getMessage('DREAMTEAM_IBLOCK_TYPE_NAME'),
            ]);
        }

        if (!$result->isSuccess()) {
            $APPLICATION->ThrowException(implode("\n", $result->getErrorMessages()));
            return false;
        }

        return true;
    }
}
