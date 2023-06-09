<?php
AddEventHandler('main', 'OnUserTypeBuildList', array('CUserTypeSectionsHtmlField', 'GetUserTypeDescription'), 5000);

class CUserTypeSectionsHtmlField
{

public static function GetUserTypeDescription()
{

return array(
// уникальный идентификатор
'USER_TYPE_ID' => 'sections_html_field',
// имя класса, методы которого формируют поведение типа
'CLASS_NAME' => 'CUserTypeSectionsHtmlField',
// название для показа в списке типов пользовательских свойств
'DESCRIPTION' => 'HTML/text',
// базовый тип на котором будут основаны операции фильтра
'BASE_TYPE' => 'string',
);
}

public static function GetDBColumnType($arUserField)
{
switch (strtolower($GLOBALS['DB']->type)) {
case 'mysql':
return 'text';
break;
}
}

public static function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
{
$result = '';

return $result;
}

public static function CheckFields($arUserField, $value)
{
$aMsg = array();
return $aMsg;
}

public static function GetEditFormHTML($arUserField, $arHtmlControl)
{
if ($arUserField["ENTITY_VALUE_ID"] < 1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"]) > 0)
$arHtmlControl["VALUE"] = htmlspecialchars($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
ob_start();
echo '<div class="html_realweb">';
    CFileMan::AddHTMLEditorFrame($arHtmlControl["NAME"], $arHtmlControl["VALUE"], "html", "html", 200, "N", 0, "", "", "s1");
    echo '</div>';
$b = ob_get_clean();



return $b;
}

public static function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
{
$html = 'Поле не может быть множественным!';
return $html;
}

public static function GetFilterHTML($arUserField, $arHtmlControl)
{
$sVal = intval($arHtmlControl['VALUE']);
$sVal = $sVal > 0 ? $sVal : '';

return CUserTypeSectionsHtmlField::GetEditFormHTML($arUserField, $arHtmlControl);
}

public static function GetAdminListViewHTML($arUserField, $arHtmlControl)
{
return '';
}

public static function GetAdminListViewHTMLMulty($arUserField, $arHtmlControl)
{
return '';
}

public static function GetAdminListEditHTML($arUserField, $arHtmlControl)
{
return '';
}

public static function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl)
{
return '';
}

public static function OnSearchIndex($arUserField)
{
return '';
}

public static function OnBeforeSave($arUserField, $value)
{
return $value;
}
}
