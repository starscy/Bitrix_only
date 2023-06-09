<?php

use Bitrix\Main\Loader;
use ScrollUp\general\TestWorked;

if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/getIDBlockByCode.php")) {
    require_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/getIDBlockByCode.php";
}

if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/autoload.php")) {
    require_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/autoload.php";
}

$eventManager = \Bitrix\Main\EventManager::getInstance();

