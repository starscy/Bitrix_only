<?php
/*
 * Файл local/modules/scrollup/include.php
 */
/*
CModule::AddAutoloadClasses(
    'scrollup',
    array(
        'ScrollUp\\Main' => 'lib/Main.php',
    )
);
*/
Bitrix\Main\Loader::registerAutoloadClasses(
    'scroll.up',
    array(
        'ScrollUp\general\TestWorked' => 'lib/general/TestWorked.php',
        'ScrollUp\general\MainWorked' => 'lib/general/MainWorked.php',
        'ScrollUp\CMain' => 'lib/CMain.php',
        'ScrollUp\Main' => 'lib/Main.php',
    )
);
