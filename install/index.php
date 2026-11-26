<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class dreamteam_test extends CModule
{
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
        ModuleManager::registerModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }
}
