<?php
if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/vendor/autoload.php")) {
    require_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/vendor/autoload.php";
}

if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/getIDBlockByCode.php")) {
    require_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/getIDBlockByCode.php";
}

if (file_exists($_SERVER["DOCUMENT_ROOT"]."/local/modules/dev.site/include.php")) {
    include $_SERVER["DOCUMENT_ROOT"]."/local/modules/dev.site/include.php";
}

if (file_exists($_SERVER["DOCUMENT_ROOT"]."/local/modules/phpdevorg.cprop/include.php")) {
    include $_SERVER["DOCUMENT_ROOT"]."/local/modules/phpdevorg.cprop/include.php";
}

$eventManager = \Bitrix\Main\EventManager::getInstance();

$eventManager->addEventHandler("iblock", "OnAfterIBlockElementAdd" , ['Only\Site\Handlers\Iblock', 'addLog']);
$eventManager->addEventHandler("iblock", "OnAfterIBlockElementUpdate" , ['Only\Site\Handlers\Iblock', 'addLog']);

