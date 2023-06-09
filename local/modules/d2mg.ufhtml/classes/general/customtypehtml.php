<?
IncludeModuleLangFile(__FILE__);

class CCustomTypeHtml extends \Bitrix\Main\UserField\Types\StringType
{
    public static function GetUserTypeDescription():array
    {

        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'mycustopProp',
           "USER_TYPE_ID" => "customhtml",
            "CLASS_NAME" => "CCustomTypeHtml",
            "DESCRIPTION" => GetMessage("PPROP_NAME"),
            "BASE_TYPE" => "string",
      //      "GetEditFormHTML" => array(__CLASS__, "GetEditFormHTML"),
    //        "GetPropertyFieldHtml" => array(__CLASS__, "GetPropertyFieldHtml"),
        );
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

    public static function OnBeforeSave($arUserField, $value)
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
}
?>