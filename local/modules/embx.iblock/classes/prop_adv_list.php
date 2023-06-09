<?
IncludeModuleLangFile(__FILE__);

class EMBXPropertyAdvElementList
{
	public static function GetPropertyDescription()
	{
		return array(
				"PROPERTY_TYPE" => "L",
				"USER_TYPE" => 'EMBXAdvList',
				"DESCRIPTION" => GetMessage('EMBXPA_LIST_DESCR'),
				"GetPropertyFieldHtml" => array(__CLASS__, "GetPropertyFieldHtml"),
				"GetPropertyFieldHtmlMulty" => array(__CLASS__,'GetPropertyFieldHtmlMulty'),
				"GetSettingsHTML" => array(__CLASS__,'GetSettingsHTML'),
				"PrepareSettings" => array(__CLASS__,'PrepareSettings')
		);
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

		if(is_array($arProperty["USER_TYPE_SETTINGS"]) && $arProperty["USER_TYPE_SETTINGS"]["is_add"] === "Y")
			$is_add = "Y";
		else
			$is_add = "N";
				
		return array(
				"size" =>  $size,
				"width" => $width,
				"multiple" => $multiple,
				"is_add" => $is_add
		);
	}

	static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
	{

		$settings = self::PrepareSettings($arProperty);


		$arPropertyFields = array(
				"HIDE" => array("ROW_COUNT", "COL_COUNT", "MULTIPLE_CNT"),
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
		<tr valign="top">
			<td>'.GetMessage("EMBXPA_IS_ADD").':</td>
			<td><input type="checkbox" name="'.$strHTMLControlName["NAME"].'[is_add]" value="Y" '.($settings["is_add"]=="Y"? 'checked': '').'></td>
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

		$settings = self::PrepareSettings($arProperty);
		if($settings["size"] > 1)
			$size = ' size="'.$settings["size"].'"';
		else
			$size = '';

		if($settings["width"] > 0)
			$width = ' style="width:'.$settings["width"].'px"';
		else
			$width = '';

		$bWasSelect = false;
		$options = self::GetOptionsHtml($arProperty, array($value["VALUE"]), $bWasSelect);
		
		$html = '<select id="ADV_PROP_'.$arProperty["ID"].'" name="'.$strHTMLControlName["VALUE"].'"'.$size.$width.'>';
		if($arProperty["IS_REQUIRED"] != "Y")
			$html .= '<option value=""'.(!$bWasSelect? ' selected': '').'>'.GetMessage("EMBXPA_NO_VALUE").'</option>';
		$html .= $options;
		$html .= '</select>';
		
		if($settings["is_add"]=="Y")
			$html .= self::GetBt($arProperty);

		return  $html;
	}
	
	static function GetBt($arProperty){

		$GLOBALS["APPLICATION"]->AddHeadScript('/bitrix/js/main/admin_tools.js');
		$GLOBALS["APPLICATION"]->AddHeadScript('/bitrix/js/iblock/iblock_edit.js');
		
		$not_setted = GetMessage('EMBXPA_NOT_SETTED');

		$html = '<input type="button" value="'. GetMessage("EMBXPA_BT_ADD") . '" 
					onclick="javascript:addSectionProperty'.$arProperty["ID"].'()">';
		$html .= <<<SCRIPT
			
			<script type="text/javascript">
			function addSectionProperty{$arProperty["ID"]}()
			{
					(new BX.CDialog({
						'content_url' : '/bitrix/admin/embx_iblock_prop.php?IBLOCK_ID={$arProperty["IBLOCK_ID"]}&ID={$arProperty["ID"]}&bxpublic=Y&from_module=iblock&return_url=section_edit&elem_id={$arProperty["CODE"]}',
						'width' : 700,
						'height' : 400,
						'buttons': [BX.CDialog.btnSave, BX.CDialog.btnCancel]
					})).Show();
			}
			function createSelect(id)
			{
				var _select = $("#" + target_id).val();
				$("#" + target_id).empty().append('<option value="">($not_setted)</option>');
				for(i in _ddd){
					$("#" + target_id).append('<option value="'+_ddd[i]['ID']+'">'+_ddd[i]['VALUE']+'</option>');	
								alert(_ddd[i]['VALUE']);	
				}
				$("#" + target_id).val(_select);
			}


						</script>		
SCRIPT;
		
		return $html;
		
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

		$settings = self::PrepareSettings($arProperty);
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
			$options = self::GetOptionsHtml($arProperty, $values, $bWasSelect);

			$html = '<input type="hidden" name="'.$strHTMLControlName["VALUE"].'[]" value="">';
			$html .= '<select multiple id="ADV_PROP_'.$arProperty["ID"].'" name="'.$strHTMLControlName["VALUE"].'[]"'.$size.$width.'>';
			if($arProperty["IS_REQUIRED"] != "Y")
				$html .= '<option value=""'.(!$bWasSelect? ' selected': '').'>'.GetMessage("EMBXPA_NO_VALUE").'</option>';
			$html .= $options;
			$html .= '</select>';
			
			if($settings["is_add"]=="Y")
				$html .= self::GetBt($arProperty);			
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
				$options = self::GetOptionsHtml($arProperty, array($value), $bWasSelect);

				$html .= '<select id="ADV_PROP_'. $arProperty["ID"] . "_" . $property_value_id.'" name="'.$strHTMLControlName["VALUE"].'['.$property_value_id.'][VALUE]"'.$size.$width.'>';
				$html .= '<option value=""'.(!$bWasSelect? ' selected': '').'>'.GetMessage("EMBXPA_NO_VALUE").'</option>';
				$html .= $options;
				$html .= '</select>';	

				$html .= '</td></tr>';
			}
			$html .= '</table>';

			$html .= '<input type="button" value="'.GetMessage("EMBXPA_ADD").'" onClick="BX.IBlock.Tools.addNewRow(\'tb'.md5($name).'\', -1)">';

			if($settings["is_add"]=="Y")
			    $html .= self::GetBt($arProperty);		
		}
		return  $html;
	}

	static function GetAdminFilterHTML($arProperty, $strHTMLControlName)
	{
		$lAdmin = new CAdminList($strHTMLControlName["TABLE_ID"]);
		$lAdmin->InitFilter(array($strHTMLControlName["VALUE"]));
		$filterValue = $GLOBALS[$strHTMLControlName["VALUE"]];

		if(isset($filterValue) && is_array($filterValue))
			$values = $filterValue;
		else
			$values = array();

		$settings = self::PrepareSettings($arProperty);
		if($settings["size"] > 1)
			$size = ' size="'.$settings["size"].'"';
		else
			$size = '';

		if($settings["width"] > 0)
			$width = ' style="width:'.$settings["width"].'px"';
		else
			$width = '';

		$bWasSelect = false;
		$options = self::GetOptionsHtml($arProperty, $values, $bWasSelect);

		$html = '<select multiple name="'.$strHTMLControlName["VALUE"].'[]"'.$size.$width.'>';
		$html .= '<option value=""'.(!$bWasSelect? ' selected': '').'>'.GetMessage("EMBXPA_ANY_VALUE").'</option>';
		$html .= $options;
		$html .= '</select>';
		return  $html;
	}

	public static function GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName)
	{
		static $cache = array();

		$strResult = '';
		$arValue['VALUE'] = intval($arValue['VALUE']);
		if (0 < $arValue['VALUE'])
		{
			if (!isset($cache[$arValue['VALUE']]))
			{
				$arFilter = array();
				$intIBlockID = intval($arProperty['LINK_IBLOCK_ID']);
				if (0 < $intIBlockID) $arFilter['IBLOCK_ID'] = $intIBlockID;
				$arFilter['ID'] = $arValue['VALUE'];
				$arFilter["ACTIVE"] = "Y";
				$arFilter["ACTIVE_DATE"] = "Y";
				$arFilter["CHECK_PERMISSIONS"] = "Y";
				$rsElements = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID","IBLOCK_ID","NAME","DETAIL_PAGE_URL"));
				$cache[$arValue['VALUE']] = $rsElements->GetNext(true,false);
			}
			if (is_array($cache[$arValue['VALUE']]))
			{
				if (isset($strHTMLControlName['MODE']) && 'CSV_EXPORT' == $strHTMLControlName['MODE'])
				{
					$strResult = $cache[$arValue['VALUE']]['ID'];
				}
				elseif (isset($strHTMLControlName['MODE']) && ('SIMPLE_TEXT' == $strHTMLControlName['MODE'] || 'ELEMENT_TEMPLATE' == $strHTMLControlName['MODE']))
				{
					$strResult = $cache[$arValue['VALUE']]["NAME"];
				}
				else
				{
					$strResult = '<a href="'.$cache[$arValue['VALUE']]["DETAIL_PAGE_URL"].'">'.htmlspecialcharsEx($cache[$arValue['VALUE']]["NAME"]).'</a>';;
				}
			}
		}
		return $strResult;
	}

	static function GetOptionsHtml($arProperty, $values, &$bWasSelect)
	{
		$options = "";
		$settings = self::PrepareSettings($arProperty);
		$bWasSelect = false;	
		
		foreach(self::GetElements($arProperty["IBLOCK_ID"], $arProperty["ID"]) as $arItem)
		{
			$options .= '<option value="'.$arItem["ID"].'"';
			if(in_array($arItem["~ID"], $values))
			{
				$options .= ' selected';
				$bWasSelect = true;
			}
			$options .= '>'.$arItem["VALUE"].'</option>';
		}

		return  $options;
	}

	static function GetElements($IBLOCK_ID, $PROPERTY_ID)
	{
		static $cache = array();
		$KEY = $IBLOCK_ID . '_' . $PROPERTY_ID;
		$IBLOCK_ID = intval($IBLOCK_ID);
		$PROPERTY_ID = intval($PROPERTY_ID);

		if(!array_key_exists($KEY, $cache))
		{
			$cache[$KEY] = array();
			if($IBLOCK_ID > 0)
			{
				$arFilter = array (
						"IBLOCK_ID"=> $IBLOCK_ID,
						"PROPERTY_ID" => $PROPERTY_ID,
				);
				$arOrder = array(
						"DEF" => "DESC",
						"SORT" => "ASC"
				);
				
				$rsItems = CIBlockPropertyEnum::GetList($arOrder, $arFilter);
				while($arItem = $rsItems->GetNext())
				{
					$cache[$KEY][] = $arItem;
				}
								
			}
		}
		return $cache[$KEY];
	}
}
?>