<?php
namespace ScrollUp\general;

use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Iblock;
use CUserTypeString;


class TestWorked  extends \Bitrix\Main\UserField\Types\StringType
{
    public static function GetUserTypeDescription():array
    {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'customhtmlTest',
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => 'ТЕСТОВАЯ ВЕЛИЧНИА',
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetEditFormHTML' => [__CLASS__, 'GetEditFormHTML'],
        );
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $arHtmlControl):string
    {
//        var_dump($arProperty) ;
//        var_dump($value);

        return 'резултатт';
    }

    public static function GetEditFormHTML(array $arUserField, ?array $arHtmlControl): string
    {

        if($arUserField["ENTITY_VALUE_ID"]<1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = htmlspecialcharsbx($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
        if($arUserField["SETTINGS"]["ROWS"] < 8)
            $arUserField["SETTINGS"]["ROWS"] = 8;

        if($arUserField['MULTIPLE'] == 'Y')
            $name = preg_replace("/[\[\]]/i", "_", $arHtmlControl["NAME"]);
        else
            $name = $arHtmlControl["NAME"];

        ob_start();

        CFileMan::AddHTMLEditorFrame(
            $name,
            $arHtmlControl["VALUE"],
            $name."_TYPE",
            strlen($arHtmlControl["VALUE"])?"html":"text",
            array(
                'height' => $arUserField['SETTINGS']['ROWS']*10,
            )
        );

        if($arUserField['MULTIPLE'] == 'Y')
            echo '<input type="hidden" name="'.$arHtmlControl["NAME"].'" >';

        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    function OnBeforeSave($arUserField, $value)
    {
        if($arUserField['MULTIPLE'] == 'Y')
        {
            foreach($_POST as $key => $val)
            {
                if( preg_match("/".$arUserField['FIELD_NAME']."_([0-9]+)_$/i", $key, $m) )
                {
                    $value = $val;
                    unset($_POST[$key]);
                    break;
                }
            }
        }
        return $value;
    }


    public static function test(){
        return 1;
    }

}

