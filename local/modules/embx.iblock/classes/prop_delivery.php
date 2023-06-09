<?
IncludeModuleLangFile(__FILE__);

class EMBXPropertyDeliveries
{

    public static function GetPropertyDescription()
    {
        return array(
            "PROPERTY_TYPE" => "S",
            "USER_TYPE" => 'EMBXDeliveries',
            "DESCRIPTION" => GetMessage('EMBXPA_DELIVERY_DESCR'),
            "GetPropertyFieldHtml" => array(__CLASS__, "GetPropertyFieldHtml"),
            "GetPropertyFieldHtmlMulty" => array(__CLASS__,'GetPropertyFieldHtmlMulty'),
            "GetSettingsHTML" => array(__CLASS__,'GetSettingsHTML'),
            "PrepareSettings" => array(__CLASS__,'PrepareSettings'),  
        	"GetAdminListViewHTML" =>   array(__CLASS__,'GetAdminListViewHTML'),
        	"GetPublicViewHTML" =>   array(__CLASS__,'GetPublicViewHTML'),
        );
    }
    
    static function getElement($value){
    	if(!empty($value["VALUE"])){    		
    		CModule::IncludeModule("sale");
    		$arFilter = array("ID" => intval($value["VALUE"]));
    		$dbDelivery = CSaleDelivery::GetList(
    				array("NAME" => "ASC"),
    				$arFilter
    		);
    		if ($arDelivery = $dbDelivery->Fetch())
    		{
    			return $arDelivery;
    		}else{
    			return false;
    		}    		
    		 
    	}else{
    		return false;
    	}
    }    
    
    static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName){
    	if($arDelivery = self::getElement($value)){
    		return '[' . $arDelivery["ID"] . '] ' . $arDelivery["NAME"];
    	}else{
    		return $value["VALUE"];
    	}    	
    }
    
    static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName){
    	if($arDelivery = self::getElement($value)){
    		return $arDelivery["NAME"];
    	}else{
    		return $value["VALUE"];
    	}    	
    }    

    static function PrepareSettings($arProperty)
    {
        $size = 0;
        if(is_array($arProperty["USER_TYPE_SETTINGS"]))
            $size = intval($arProperty["USER_TYPE_SETTINGS"]["size"]);
        if($size <= 0)
            $size = 1;

        $width = 0;
        if(is_array($arProperty["USER_TYPE_SETTINGS"]))
            $width = intval($arProperty["USER_TYPE_SETTINGS"]["width"]);
        if($width <= 0)
            $width = 0;

        if(is_array($arProperty["USER_TYPE_SETTINGS"]) && $arProperty["USER_TYPE_SETTINGS"]["multiple"] === "Y")
            $multiple = "Y";
        else
            $multiple = "N";

        return array(
            "size" =>  $size,
            "width" => $width,
            "multiple" => $multiple,
        );
    }

    static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        $settings = EMBXPropertyDeliveries::PrepareSettings($arProperty);

        $arPropertyFields = array(
            "HIDE" => array("ROW_COUNT", "COL_COUNT", "DEFAULT_VALUE", "MULTIPLE_CNT", "WITH_DESCRIPTION", "FILTRABLE", "SEARCHABLE"),
        );

        return '
	<tr valign="top">
	<td>'.GetMessage("EMBXPA_SETTING_SIZE").':</td>
	<td><input type="text" size="5" name="'.$strHTMLControlName["NAME"].'[size]" value="'.$settings["size"].'"></td>
	</tr>
	<tr valign="top">
	<td>'.GetMessage("EMBXPA_SETTING_WIDTH").':</td>
	<td><input type="text" size="5" name="'.$strHTMLControlName["NAME"].'[width]" value="'.$settings["width"].'">px</td>
	</tr>
	<tr valign="top">
	<td>'.GetMessage("EMBXPA_SETTING_MULTIPLE").':</td>
	<td><input type="checkbox" name="'.$strHTMLControlName["NAME"].'[multiple]" value="Y" '.($settings["multiple"]=="Y"? 'checked': '').'></td>
	</tr>
	';
    }

    //PARAMETERS:
    //$arProperty - b_iblock_property.*
    //$value - array("VALUE","DESCRIPTION") -- here comes HTML form value
    //strHTMLControlName - array("VALUE","DESCRIPTION")
    //return:
    //safe html
    static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $settings = EMBXPropertyDeliveries::PrepareSettings($arProperty);
        if($settings["size"] > 1)
            $size = ' size="'.$settings["size"].'"';
        else
            $size = '';

        if($settings["width"] > 0)
            $width = ' style="width:'.$settings["width"].'px"';
        else
            $width = '';

        if($settings["multiple"]=="Y"){
            $multiple = 'multiple';
            $name = $strHTMLControlName["VALUE"] . '[]';
        }else{
            $multiple = '';
            $name = $strHTMLControlName["VALUE"];
        }

        $bWasSelect = false;
        $options = EMBXPropertyDeliveries::GetOptionsHtml($arProperty, (is_array($value["VALUE"])) ?  $value["VALUE"] : array($value["VALUE"]), $bWasSelect);

        $html = '<select '.$multiple.' name="'.$name.'"'.$size.$width.'>';
        if($arProperty["IS_REQUIRED"] != "Y")
            $html .= '<option value=""'.(!$bWasSelect? ' selected': '').'>'.GetMessage("EMBXPA_NO_VALUE").'</option>';
        $html .= $options;
        $html .= '</select>';


        return  $html;
    }

    static function GetPropertyFieldHtmlMulty($arProperty, $value, $strHTMLControlName)
    {
        $max_n = 0;
        $values = array();
        if(is_array($value))
        {
            foreach($value as $property_value_id => $arValue)
            {
                $values[$property_value_id] = $arValue["VALUE"];
                if(preg_match("/^n(\\d+)$/", $property_value_id, $match))
                {
                    if($match[1] > $max_n)
                        $max_n = intval($match[1]);
                }
            }
        }

        $settings = EMBXPropertyDeliveries::PrepareSettings($arProperty);
        if($settings["size"] > 1)
            $size = ' size="'.$settings["size"].'"';
        else
            $size = '';

        if($settings["width"] > 0)
            $width = ' style="width:'.$settings["width"].'px"';
        else
            $width = '';

        if($settings["multiple"]=="Y")
        {
            $bWasSelect = false;
            $options = EMBXPropertyDeliveries::GetOptionsHtml($arProperty, $values, $bWasSelect);

            $html = '<input type="hidden" name="'.$strHTMLControlName["VALUE"].'[]" value="">';
            $html .= '<select multiple name="'.$strHTMLControlName["VALUE"].'[]"'.$size.$width.'>';
            if($arProperty["IS_REQUIRED"] != "Y")
                $html .= '<option value=""'.(!$bWasSelect? ' selected': '').'>'.GetMessage("EMBXPA_NO_VALUE").'</option>';
            $html .= $options;
            $html .= '</select>';
        }
        else
        {
            if(end($values) != "" || substr(key($values), 0, 1) != "n")
                $values["n".($max_n+1)] = "";

            $name = $strHTMLControlName["VALUE"]."VALUE";

            $html = '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';
            foreach($values as $property_value_id=>$value)
            {
                $html .= '<tr><td>';

                $bWasSelect = false;
                $options = EMBXPropertyDeliveries::GetOptionsHtml($arProperty, array($value), $bWasSelect);

                $html .= '<select name="'.$strHTMLControlName["VALUE"].'['.$property_value_id.'][VALUE]"'.$size.$width.'>';
                $html .= '<option value=""'.(!$bWasSelect? ' selected': '').'>'.GetMessage("EMBXPA_NO_VALUE").'</option>';
                $html .= $options;
                $html .= '</select>';

                $html .= '</td></tr>';
            }
            $html .= '</table>';

            $html .= '<input type="button" value="'.GetMessage("EMBXPA_ADD").'" onClick="if(window.addNewRow){addNewRow(\'tb'.md5($name).'\', -1)}else{addNewTableRow(\'tb'.md5($name).'\', 1, /\[(n)([0-9]*)\]/g, 2)}">';
        }
        return  $html;
    }

    static function GetAdminFilterHTML($arProperty, $strHTMLControlName)
    {
        if(isset($_REQUEST[$strHTMLControlName["VALUE"]]) && is_array($_REQUEST[$strHTMLControlName["VALUE"]]))
            $values = $_REQUEST[$strHTMLControlName["VALUE"]];
        else
            $values = array();

        $settings = EMBXPropertyDeliveries::PrepareSettings($arProperty);
        if($settings["size"] > 1)
            $size = ' size="'.$settings["size"].'"';
        else
            $size = '';

        if($settings["width"] > 0)
            $width = ' style="width:'.$settings["width"].'px"';
        else
            $width = '';

        $bWasSelect = false;
        $options = EMBXPropertyDeliveries::GetOptionsHtml($arProperty, $values, $bWasSelect);

        $html = '<select multiple name="'.$strHTMLControlName["VALUE"].'[]"'.$size.$width.'>';
        $html .= '<option value=""'.(!$bWasSelect? ' selected': '').'>'.GetMessage("EMBXPA_ANY_VALUE").'</option>';
        $html .= $options;
        $html .= '</select>';
        return  $html;
    }

    static function GetOptionsHtml($arProperty, $values, &$bWasSelect)
    {
        $options = "";
        $settings = EMBXPropertyDeliveries::PrepareSettings($arProperty);
        $bWasSelect = false;

        foreach(EMBXPropertyDeliveries::GetElements() as $arItem)
        {
            $options .= '<option value="'.$arItem["ID"].'"';
            if(in_array($arItem["ID"], $values))
            {
                $options .= ' selected';
                $bWasSelect = true;
            }
            $options .= '>['. $arItem["ID"] . '] ' . $arItem["NAME"].'</option>';
        }

        return  $options;
    }

	static function GetElements()
	{
		static $cache = array();

        CModule::IncludeModule("sale");

		if(empty($cache))
		{
            $arFilter = array("ACTIVE" => "Y");           
            $dbDelivery = CSaleDelivery::GetList(
                array("NAME" => "ASC"),
                $arFilter
            );
            while ($arDelivery = $dbDelivery->Fetch())
            {
                $cache[] = array("ID" => $arDelivery["ID"], "NAME" => $arDelivery["NAME"]);
            }
		}

		return $cache;
	}
}
?>