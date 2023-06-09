
<?php

use Bitrix\Main\Loader;

//Автозагрузка наших классов
Loader::registerAutoLoadClasses(null, [
    'lib\usertype\CUserTypeTimesheet' => '/local/php_interface/lib/' . 'usertype/CUserTypeTimesheet.php',
]);
