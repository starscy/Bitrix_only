<?php
/*
 * Файл local/modules/scrollup/install/unstep.php
 */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid()){
    return;
}

if ($errorException = $APPLICATION->getException()) {
    // ошибка при удалении модуля
    CAdminMessage::showMessage(
        Loc::getMessage('SCROLLUP_UNINSTALL_FAILED').': '.$errorException->GetString()
    );
} else {
    // модуль успешно удален
    CAdminMessage:showNote(
        Loc::getMessage('SCROLLUP_UNINSTALL_SUCCESS')
    );
}
?>

<form action="<?= $APPLICATION->getCurPage(); ?>"> <!-- Кнопка возврата к списку модулей -->
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID; ?>" />
    <input type="submit" value="<?= Loc::getMessage('SCROLLUP_RETURN_MODULES'); ?>">
</form>