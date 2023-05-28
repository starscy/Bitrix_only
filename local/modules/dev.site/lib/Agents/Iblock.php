<?php

namespace Only\Site\Agents;


class Iblock
{
    public static function clearOldLogs()
    {
        $bd = \CIBlockElement::GetList(['TIMESTAMP_X' => 'DESC'],['IBLOCK_ID' => 21],false, false, ['ID']);
        $count = 0;
        while ($it = $bd->GetNext()) {
            $count++;
            if($count > 10) {
                \CIBlockElement::Delete($it['ID']);
            }
        }
        return "Only\Site\Agents\Iblock::clearOldLogs();";
    }

    public static function example()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = \Only\Site\Helpers\IBlock::getIblockID('QUARRIES_SEARCH', 'SYSTEM');
            $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('SHORT'));
            $rsLogs = \CIBlockElement::GetList(['TIMESTAMP_X' => 'ASC'], [
                'IBLOCK_ID' => $iblockId,
                '<TIMESTAMP_X' => date($format, strtotime('-1 months')),
            ], false, false, ['ID', 'IBLOCK_ID']);
            while ($arLog = $rsLogs->Fetch()) {
                \CIBlockElement::Delete($arLog['ID']);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
