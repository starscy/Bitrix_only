<?
define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", false);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$bFullForm = isset($_REQUEST["IBLOCK_ID"]) && isset($_REQUEST["ID"]);
$bSectionPopup = isset($_REQUEST["return_url"]) && ($_REQUEST["return_url"] === "section_edit");
$bReload = isset($_REQUEST["action"]) && $_REQUEST["action"] === "reload";

CJSCore::Init(array("jquery"));

if (
	('POST' == $_SERVER['REQUEST_METHOD'])
	&& (false == isset($_REQUEST['saveresult']))
	&& (false == isset($_REQUEST['IBLOCK_ID']))
)
	CUtil::JSPostUnescape();
elseif ($bSectionPopup)
	CUtil::JSPostUnescape();

global $DB;
global $APPLICATION;
global $USER;

define('DEF_LIST_VALUE_COUNT',5);

/*
* $intPropID - ID value or n0...nX
* $arPropInfo = array(
* 		ID
* 		XML_ID
* 		VALUE
* 		SORT
* 		DEF = Y/N
* 		MULTIPLE = Y/N
* )
*/
function __AddListValueIDCell($intPropID,$arPropInfo)
{
	return (0 < intval($intPropID) ? $intPropID : '&nbsp;');
}

function __AddListValueXmlIDCell($intPropID,$arPropInfo)
{
	return '<input type="text" name="PROPERTY_VALUES['.$intPropID.'][XML_ID]" id="PROPERTY_VALUES_XML_'.$intPropID.'" value="'.htmlspecialcharsbx($arPropInfo['XML_ID']).'" size="15" maxlength="200" style="width:90%">';
}

function __AddListValueValueCell($intPropID,$arPropInfo)
{
	return '<input type="text" name="PROPERTY_VALUES['.$intPropID.'][VALUE]" id="PROPERTY_VALUES_VALUE_'.$intPropID.'" value="'.htmlspecialcharsbx($arPropInfo['VALUE']).'" size="35" maxlength="255" style="width:90%">';
}

function __AddListValueSortCell($intPropID,$arPropInfo)
{
	return '<input type="text" name="PROPERTY_VALUES['.$intPropID.'][SORT]" id="PROPERTY_VALUES_SORT_'.$intPropID.'" value="'.intval($arPropInfo['SORT']).'" size="5" maxlength="11">';
}

function __AddListValueDefCell($intPropID,$arPropInfo)
{
	return '<input type="'.('Y' == $arPropInfo['MULTIPLE'] ? 'checkbox' : 'radio').'" name="PROPERTY_VALUES_DEF'.('Y' == $arPropInfo['MULTIPLE'] ? '[]' : '').'" id="PROPERTY_VALUES_DEF_'.$arPropInfo['ID'].'" value="'.$arPropInfo['ID'].'" '.('Y' == $arPropInfo['DEF'] ? 'checked="checked"' : '').'>';
}

function __AddListValueRow($intPropID,$arPropInfo)
{
	return '<tr><td class="bx-digit-cell">'.__AddListValueIDCell($intPropID,$arPropInfo).'</td>
	<td>'.__AddListValueXmlIDCell($intPropID,$arPropInfo).'</td>
	<td>'.__AddListValueValueCell($intPropID,$arPropInfo).'</td>
	<td style="text-align:center">'.__AddListValueSortCell($intPropID,$arPropInfo).'</td>
	<td style="text-align:center">'.__AddListValueDefCell($intPropID,$arPropInfo).'</td></tr>';
}

$arDisabledPropFields = array(
	'ID',
	'IBLOCK_ID',
	'TIMESTAMP_X',
	'TMP_ID',
	'VERSION',
);

$arDefPropInfo = array(
	'ID' => 'ntmp_xxx',
	'XML_ID' => '',
	'VALUE' => '',
	'SORT' => '500',
	'DEF' => 'N',
	'MULTIPLE' => 'N',
);

$arDefPropInfo = array(
	'ID' => 0,
	'IBLOCK_ID' => 0,
	'FILE_TYPE' => '',
	'LIST_TYPE' => 'L',
	'ROW_COUNT' => '1',
	'COL_COUNT' => '30',
	'LINK_IBLOCK_ID' => '0',
	'DEFAULT_VALUE' => '',
	'USER_TYPE_SETTINGS' => false,
	'WITH_DESCRIPTION' => '',
	'SEARCHABLE' => '',
	'FILTRABLE' => '',
	'ACTIVE' => 'Y',
	'MULTIPLE_CNT' => '5',
	'XML_ID' => '',
	'PROPERTY_TYPE' => 'S',
	'NAME' => '',
	'HINT' => '',
	'USER_TYPE' => '',
	'MULTIPLE' => 'N',
	'IS_REQUIRED' => 'N',
	'SORT' => '500',
	'CODE' => '',
	'SHOW_DEL' => 'N',
	'VALUES' => false,
	'SECTION_PROPERTY' => $bSectionPopup? "N": "Y",
	'SMART_FILTER' => 'N',
);

$arHiddenPropFields = array(
	'IBLOCK_ID',
	'FILE_TYPE',
	'LIST_TYPE',
	'ROW_COUNT',
	'COL_COUNT',
	'LINK_IBLOCK_ID',
	'DEFAULT_VALUE',
	'USER_TYPE_SETTINGS',
	'WITH_DESCRIPTION',
	'SEARCHABLE',
	'FILTRABLE',
	'MULTIPLE_CNT',
	'HINT',
	'XML_ID',
	'VALUES',
	'SECTION_PROPERTY',
	'SMART_FILTER',
);

if ($_SERVER["REQUEST_METHOD"] == "POST" && !check_bitrix_sessid())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

if(isset($_REQUEST["PARAMS"]['IBLOCK_ID']))
	$intIBlockID = intval($_REQUEST["PARAMS"]['IBLOCK_ID']);
elseif(isset($_REQUEST["IBLOCK_ID"]))
	$intIBlockID = intval($_REQUEST["IBLOCK_ID"]);
else
	$intIBlockID = false;

if ($intIBlockID < 0 || $intIBlockID === false)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	echo ShowError(GetMessage("BT_ADM_IEP_IBLOCK_ID_IS_INVALID"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
elseif ($intIBlockID > 0)
{
	$rsIBlocks = CIBlock::GetList(array(), array(
		"ID" => $intIBlockID,
		"CHECK_PERMISSIONS" => "N",
	));
	$arIBlock = $rsIBlocks->Fetch();
	if ($arIBlock)
	{
		if (!CIBlockRights::UserHasRightTo($intIBlockID, $intIBlockID, "element_edit"))
		{
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
			$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
			die();
		}
	}
	else
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		echo ShowError(str_replace('#ID#',$intIBlockID,GetMessage("BT_ADM_IEP_IBLOCK_NOT_EXISTS")));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
}

if(isset($_REQUEST["PARAMS"]['ID']))
	$str_PROPERTY_ID = htmlspecialcharsbx($_REQUEST["PARAMS"]['ID']);
elseif(isset($_REQUEST['ID']))
	$str_PROPERTY_ID = htmlspecialcharsbx($_REQUEST['ID']);
else
	$str_PROPERTY_ID = "";


if(isset($_REQUEST["PARAMS"]['elem_id']))
	$str_elem_id = htmlspecialcharsbx($_REQUEST["PARAMS"]['elem_id']);
elseif(isset($_REQUEST['elem_id']))
	$str_elem_id = htmlspecialcharsbx($_REQUEST['elem_id']);
else
	$str_elem_id = "";


if (!strlen($str_PROPERTY_ID))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	echo ShowError(GetMessage("BT_ADM_IEP_PROPERTY_ID_IS_ABSENT"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$arListValues = array();

if(CModule::IncludeModule('highloadblock') && isset($_POST['PROPERTY_DIRECTORY_VALUES']) && is_array($_POST['PROPERTY_DIRECTORY_VALUES']))
{
	if(isset($_POST["HLB_NEW_TITLE"]) && $_POST["PROPERTY_USER_TYPE_SETTINGS"]["TABLE_NAME"] == '-1')
	{
		$highBlockName = trim($_POST["HLB_NEW_TITLE"]);
		if($highBlockName == '')
		{
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
			CAdminMessage::ShowOldStyleError(GetMessage("BT_ADM_IEP_HBLOCK_NAME_IS_ABSENT"));
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
			die();
		}
		$highBlockName = strtoupper(substr($highBlockName, 0, 1)).substr($highBlockName, 1);
		if(!preg_match('/^[A-Z][A-Za-z0-9]*$/', $highBlockName))
		{
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
			CAdminMessage::ShowOldStyleError(GetMessage("BT_ADM_IEP_HBLOCK_NAME_IS_INVALID"));
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
			die();
		}
		$data = array(
			'NAME' => $highBlockName,
			'TABLE_NAME' => 'b_'.strtolower($_POST["HLB_NEW_TITLE"])
		);

		$result = Bitrix\Highloadblock\HighloadBlockTable::add($data);

		$highBlockID = $result->getId();
		$_POST["PROPERTY_USER_TYPE_SETTINGS"]["TABLE_NAME"] = $data['TABLE_NAME'];
		$arFieldsName = $_POST['PROPERTY_DIRECTORY_VALUES'][0];
		$arFieldsName['UF_DEF'] = '';
		$arFieldsName['UF_FILE'] = '';
		$obUserField = new CUserTypeEntity();
		$intSortStep = 100;
		foreach($arFieldsName as $fieldName => $fieldValue)
		{
			if ('UF_DELETE' == $fieldName)
				continue;

			$fieldMandatory = 'N';
			switch($fieldName)
			{
				case 'UF_NAME':
				case 'UF_XML_ID':
					$fieldType = 'string';
					$fieldMandatory = 'Y';
					break;
				case 'UF_LINK':
				case 'UF_DESCRIPTION':
				case 'UF_FULL_DESCRIPTION':
					$fieldType = 'string';
					break;
				case 'UF_SORT':
					$fieldType = 'integer';
					break;
				case 'UF_FILE':
					$fieldType = 'file';
					break;
				case 'UF_DEF':
					$fieldType = 'boolean';
					break;
				default:
					$fieldType = 'string';
			}
			$arUserField = array(
				"ENTITY_ID" => "HLBLOCK_".$highBlockID,
				"FIELD_NAME" => $fieldName,
				"USER_TYPE_ID" => $fieldType,
				"XML_ID" => "",
				"SORT" => $intSortStep,
				"MULTIPLE" => "N",
				"MANDATORY" => $fieldMandatory,
				"SHOW_FILTER" => "N",
				"SHOW_IN_LIST" => "Y",
				"EDIT_IN_LIST" => "Y",
				"IS_SEARCHABLE" => "N",
				"SETTINGS" => array(),
			);
			if(isset($_POST['PROPERTY_USER_TYPE_SETTINGS']['LANG'][$fieldName]))
			{
				$arUserField["EDIT_FORM_LABEL"] = $arUserField["LIST_COLUMN_LABEL"] = $arUserField["LIST_FILTER_LABEL"] = array(LANGUAGE_ID => $_POST['PROPERTY_USER_TYPE_SETTINGS']['LANG'][$fieldName]);
			}
			$obUserField->Add($arUserField);
			$intSortStep += 100;
		}
	}
	$arImageResult = array();
	if(isset($_FILES['PROPERTY_DIRECTORY_VALUES']) && is_array($_FILES['PROPERTY_DIRECTORY_VALUES']))
		CFile::ConvertFilesToPost($_FILES['PROPERTY_DIRECTORY_VALUES'], $arImageResult);
	if($_POST["PROPERTY_USER_TYPE_SETTINGS"]["TABLE_NAME"] == '-1' && isset($result) && $result->isSuccess())
	{
		$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($highBlockID)->fetch();
	}
	else
	{
		$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList(array("filter" => array("TABLE_NAME" => $_POST["PROPERTY_USER_TYPE_SETTINGS"]["TABLE_NAME"])))->fetch();
	}
	$entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
	$entityDataClass = $entity->getDataClass();
	$fieldsList = $entityDataClass::getMap();
	if (count($fieldsList) == 1 && isset($fieldsList['ID']))
	{
		$fieldsList = $entityDataClass::getEntity()->getFields();
	}

	foreach($_POST['PROPERTY_DIRECTORY_VALUES'] as $dirKey => $arDirValue)
	{
		if(isset($arDirValue["UF_DELETE"]))
		{
			if($arDirValue["UF_DELETE"] === 'Y')
				if(isset($arDirValue["ID"]) && intval($arDirValue["ID"]) > 0)
				{
					$entityDataClass::delete($arDirValue["ID"]);
					continue;
				}
			unset($arDirValue["UF_DELETE"]);
		}
		if(!is_array($arDirValue) || !isset($arDirValue['UF_NAME']) || '' == trim($arDirValue['UF_NAME']))
			continue;
		if((isset($arImageResult[$dirKey]["FILE"]) && is_array($arImageResult[$dirKey]["FILE"]) && $arImageResult[$dirKey]["FILE"]['name'] != '') || (isset($_POST['PROPERTY_DIRECTORY_VALUES_del'][$dirKey]["FILE"]) && $_POST['PROPERTY_DIRECTORY_VALUES_del'][$dirKey]["FILE"] == 'Y'))
			$arDirValue['UF_FILE'] = $arImageResult[$dirKey]["FILE"];

		if($arDirValue["ID"] == $_POST['PROPERTY_VALUES_DEF'])
			$arDirValue['UF_DEF'] = true;
		else
			$arDirValue['UF_DEF'] = false;
		if(!isset($arDirValue["UF_XML_ID"]) || $arDirValue["UF_XML_ID"] == '')
			$arDirValue['UF_XML_ID'] = randString(8);


		if ($_POST["PROPERTY_USER_TYPE_SETTINGS"]["TABLE_NAME"] == '-1' && isset($result) && $result->isSuccess())
		{
			$entityDataClass::add($arDirValue);
		}
		else
		{
			if (isset($arDirValue["ID"]) && $arDirValue["ID"] > 0)
			{
				$rsData = $entityDataClass::getList(array());
				while($arData = $rsData->fetch())
				{
					$arAddField = array();
					if(!isset($arData["UF_DESCRIPTION"]))
					{
						$arAddField[] = 'UF_DESCRIPTION';
					}
					if(!isset($arData["UF_FULL_DESCRIPTION"]))
					{
						$arAddField[] = 'UF_FULL_DESCRIPTION';
					}
					$obUserField = new CUserTypeEntity();
					foreach($arAddField as $addField)
					{
						$arUserField = array(
							"ENTITY_ID" => "HLBLOCK_".$hlblock["ID"],
							"FIELD_NAME" => $addField,
							"USER_TYPE_ID" => 'string',
							"XML_ID" => "",
							"SORT" => 100,
							"MULTIPLE" => "N",
							"MANDATORY" => "N",
							"SHOW_FILTER" => "N",
							"SHOW_IN_LIST" => "Y",
							"EDIT_IN_LIST" => "Y",
							"IS_SEARCHABLE" => "N",
							"SETTINGS" => array(),
						);
						if(isset($_POST['PROPERTY_USER_TYPE_SETTINGS']['LANG'][$addField]))
						{
							$arUserField["EDIT_FORM_LABEL"] = $arUserField["LIST_COLUMN_LABEL"] = $arUserField["LIST_FILTER_LABEL"] = array(LANGUAGE_ID => $_POST['PROPERTY_USER_TYPE_SETTINGS']['LANG'][$addField]);
						}
						$obUserField->Add($arUserField);
					}
					if($arDirValue["ID"] == $arData["ID"])
					{
						unset($arDirValue["ID"]);
						$dirValueKeys = array_keys($arDirValue);
						foreach ($dirValueKeys as $oneKey)
						{
							if (!isset($fieldsList[$oneKey]))
								unset($arDirValue[$oneKey]);
						}
						if (isset($oneKey))
							unset($oneKey);
						if (!empty($arDirValue))
						{
							$entityDataClass::update($arData["ID"], $arDirValue);
						}
					}
				}
			}
			else
			{
				if (array_key_exists("ID", $arDirValue))
					unset($arDirValue["ID"]);
				$dirValueKeys = array_keys($arDirValue);
				foreach ($dirValueKeys as $oneKey)
				{
					if (!isset($fieldsList[$oneKey]))
						unset($arDirValue[$oneKey]);
				}
				if (isset($oneKey))
					unset($oneKey);
				if (!empty($arDirValue))
				{
					$entityDataClass::add($arDirValue);
				}
			}
		}
	}
}
if (isset($_POST['PROPERTY_VALUES']) && is_array($_POST['PROPERTY_VALUES']))
{
	$boolDefCheck = false;
	if ('Y' == $_POST['PROPERTY_MULTIPLE'])
	{
		$boolDefCheck = (isset($_POST['PROPERTY_VALUES_DEF']) && is_array($_POST['PROPERTY_VALUES_DEF']));
	}
	else
	{
		$boolDefCheck = isset($_POST['PROPERTY_VALUES_DEF']);
	}
	$intNewKey = 0;
	foreach ($_POST['PROPERTY_VALUES'] as $key => $arValue)
	{
		if (!is_array($arValue) || !isset($arValue['VALUE']) || '' == trim($arValue['VALUE']))
			continue;
		$arListValues[(0 < intval($key) ? $key : 'n'.$intNewKey)] = array(
			'ID' => (0 < intval($key) ? $key : 'n'.$intNewKey),
			'VALUE' => strval($arValue['VALUE']),
			'XML_ID' => (isset($arValue['XML_ID']) ? strval($arValue['XML_ID']) : ''),
			'SORT' => (isset($arValue['SORT']) ? intval($arValue['SORT']) : 500),
			'DEF' => ($boolDefCheck ?
						('Y' == $_POST['PROPERTY_MULTIPLE'] ?
							(in_array($key, $_POST['PROPERTY_VALUES_DEF']) ? 'Y' : 'N') :
							($key == $_POST['PROPERTY_VALUES_DEF'] ? 'Y' : 'N')) :
						'N'),
		);
		if (0 >= intval($key))
			$intNewKey++;
	}
}

if (1 != preg_match('/^n\d+$/',$str_PROPERTY_ID))
{
	$str_PROPERTY_IDCheck = intval($str_PROPERTY_ID);
	if (0 == $intIBlockID || ($str_PROPERTY_IDCheck.'|' != $str_PROPERTY_ID.'|') || 0 >= $str_PROPERTY_IDCheck)
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		echo ShowError(GetMessage("BT_ADM_IEP_PROPERTY_ID_IS_ABSENT"));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
	else
	{
		$str_PROPERTY_ID = $str_PROPERTY_IDCheck;
		unset($str_PROPERTY_IDCheck);
		$rsProps = CIBlockProperty::GetByID($str_PROPERTY_ID, $intIBlockID);
		if (!($arPropCheck = $rsProps->Fetch()))
		{
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
			echo ShowError(str_replace('#ID#',$str_PROPERTY_ID,GetMessage("BT_ADM_IEP_PROPERTY_IS_NOT_EXISTS")));
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
			die();
		}
	}
}

$bVarsFromForm = $bReload;
$message = false;
$strWarning = "";

if(!$bReload && $_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST["save"]) || isset($_POST["apply"])))
{

	if(!empty($arListValues)){

		$ibp = new CIBlockProperty;
		$res = $ibp->UpdateEnum($str_PROPERTY_ID, $arListValues);

		$arListValues = array();
		$db_enum_list = CIBlockProperty::GetPropertyEnum($str_PROPERTY_ID, Array('SORT'=>'ASC'));
		while($ar_enum = $db_enum_list->Fetch())
		{
			$arListValues[$ar_enum['ID']] = Array('SORT'=>$ar_enum["SORT"], 'ID'=>$ar_enum["ID"], 'VALUE'=>$ar_enum['VALUE']);
		}	
	}

	if(!$res)
	{
		$strWarning .= $ibp->LAST_ERROR;
		$bVarsFromForm = true;
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("admin_lib_error"), $e);
	}
	else
	{
		if(strlen($apply)<=0)
		{
			if($bSectionPopup)
			{
				if($arFields['PROPERTY_TYPE'] == "S" && !$arFields['USER_TYPE'])
					$type = GetMessage("IBLOCK_PROP_S");
				elseif($arFields['PROPERTY_TYPE'] == "N" && !$arFields['USER_TYPE'])
					$type = GetMessage("IBLOCK_PROP_N");
				elseif($arFields['PROPERTY_TYPE'] == "L" && !$arFields['USER_TYPE'])
					$type = GetMessage("IBLOCK_PROP_L");
				elseif($arFields['PROPERTY_TYPE'] == "F" && !$arFields['USER_TYPE'])
					$type = GetMessage("IBLOCK_PROP_F");
				elseif($arFields['PROPERTY_TYPE'] == "G" && !$arFields['USER_TYPE'])
					$type = GetMessage("IBLOCK_PROP_G");
				elseif($arFields['PROPERTY_TYPE'] == "E" && !$arFields['USER_TYPE'])
					$type = GetMessage("IBLOCK_PROP_E");
				elseif($arFields['USER_TYPE'] && is_array($ar = CIBlockProperty::GetUserType($arFields['USER_TYPE'])))
					$type = htmlspecialcharsex($ar["DESCRIPTION"]);
				else
					$type = GetMessage("IBSEC_E_PROP_TYPE_S");
				
				echo '<script type="text/javascript">
                        $("[id^=\'ADV_PROP_'.$str_PROPERTY_ID.'\']").each(function(){		
    						var _select = $(this).val();
    						$(this).empty().append(\'<option value="">('.GetMessage('EMBXPA_NOT_SETTED').')</option>\');';
    				
    						foreach($arListValues as $v){
    							echo '$(this).append(\'<option value="'.$v['ID'].'">'.$v['VALUE'].'</option>\');' . "\n";
    						}
    						echo '$(this).val(_select);
                        });
				
						top.BX.closeWait();
						top.BX.WindowManager.Get().AllowClose();
						top.BX.WindowManager.Get().Close();
					</script>';
				die();
			}

			if(strlen($return_url)>0)
				LocalRedirect($return_url);
			else
				LocalRedirect('iblock_property_admin.php?lang='.LANGUAGE_ID.'&IBLOCK_ID='.$intIBlockID.($_REQUEST["admin"]=="Y"? "&admin=Y": "&admin=N"));
		}
		LocalRedirect("iblock_edit_property.php?lang=".LANGUAGE_ID."&IBLOCK_ID=".$IBLOCK_ID."&find_section_section=".intval($find_section_section).'&ID='.intval($str_PROPERTY_ID).(strlen($return_url)>0?"&return_url=".UrlEncode($return_url):"").($_REQUEST["admin"]=="Y"? "&admin=Y": "&admin=N"));
	}
}

$strReceiver = '';

if (isset($_REQUEST["PARAMS"]['RECEIVER']))
	$strReceiver = preg_replace("/[^a-zA-Z0-9_:]/", "", htmlspecialcharsbx(($_REQUEST["PARAMS"]['RECEIVER'])));

if (isset($_REQUEST['saveresult']))
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	unset($_POST['saveresult']);
	$PARAMS = $_POST['PARAMS'];
	unset($_POST['PARAMS']);

	$arProperty = array();

	$arFieldsList = $DB->GetTableFieldsList("b_iblock_property");
	foreach ($arFieldsList as $strFieldName)
	{
		if (!in_array($strFieldName,$arDisabledPropFields))
		{
			if (isset($_POST['PROPERTY_'.$strFieldName]))
			{
				$arProperty[$strFieldName] = $_POST['PROPERTY_'.$strFieldName];
			}
			else
				$arProperty[$strFieldName] = $arDefPropInfo[$strFieldName];
		}
	}

	if (isset($_POST['PROPERTY_SECTION_PROPERTY']))
		$arProperty['SECTION_PROPERTY'] = $_POST['PROPERTY_SECTION_PROPERTY'];
	else
		$arProperty['SECTION_PROPERTY'] = $arDefPropInfo['SECTION_PROPERTY'];

	if (isset($_POST['PROPERTY_SMART_FILTER']))
		$arProperty['SMART_FILTER'] = $_POST['PROPERTY_SMART_FILTER'];
	else
		$arProperty['SMART_FILTER'] = $arDefPropInfo['SMART_FILTER'];

	$arProperty['MULTIPLE'] = ('Y' == $arProperty['MULTIPLE'] ? 'Y' : 'N');
	$arProperty['IS_REQUIRED'] = ('Y' == $arProperty['IS_REQUIRED'] ? 'Y' : 'N');
	$arProperty['FILTRABLE'] = ('Y' == $arProperty['FILTRABLE'] ? 'Y' : 'N');
	$arProperty['SEARCHABLE'] = ('Y' == $arProperty['SEARCHABLE'] ? 'Y' : 'N');
	$arProperty['ACTIVE'] = ('Y' == $arProperty['ACTIVE'] ? 'Y' : 'N');
	$arProperty['SECTION_PROPERTY'] = ('N' == $arProperty['SECTION_PROPERTY'] ? 'N' : 'Y');
	$arProperty['SMART_FILTER'] = ('Y' == $arProperty['SMART_FILTER'] ? 'Y' : 'N');
	$arProperty['MULTIPLE_CNT'] = intval($arProperty['MULTIPLE_CNT']);
	if (0 >= $arProperty['MULTIPLE_CNT'])
		$arProperty['MULTIPLE_CNT'] = DEF_LIST_VALUE_COUNT;
	$arProperty['WITH_DESCRIPTION'] = ('Y' == $arProperty['WITH_DESCRIPTION'] ? 'Y' : 'N');

	if(!empty($arListValues))
		$arProperty["VALUES"] = $arListValues;

	$arHidden = array();
	foreach ($arHiddenPropFields as &$strPropField)
	{
		if (isset($arProperty[$strPropField]))
		{
			$arHidden[$strPropField] = $arProperty[$strPropField];
			unset($arProperty[$strPropField]);
		}
	}
	$arProperty['PROPINFO'] = base64_encode(serialize($arHidden));

	$strResult = CUtil::PhpToJsObject($arProperty);
	?><script type="text/javascript">
	arResult = <? echo $strResult; ?>;
	if (top.<? echo $strReceiver; ?>)
	{
		top.<? echo $strReceiver; ?>.SetPropInfo('<? echo $PARAMS['ID']; ?>',arResult,'<? echo bitrix_sessid(); ?>');
	}
	top.BX.closeWait(); top.BX.WindowManager.Get().AllowClose(); top.BX.WindowManager.Get().Close();
	</script><?
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
	die();
}

$aTabs = array();
$tabControl = null;

if(!$bFullForm)
{
	$arProperty = array();
	$PROPERTY = $_POST['PROP'];
	$PARAMS = $_POST['PARAMS'];

	if ((isset($PARAMS['TITLE'])) && ('' != $PARAMS['TITLE']))
	{
		$APPLICATION->SetTitle($PARAMS['TITLE']);
	}

	$arFieldsList = $DB->GetTableFieldsList("b_iblock_property");
	foreach ($arFieldsList as $strFieldName)
	{
		if (!in_array($strFieldName,$arDisabledPropFields))
			$arProperty[$strFieldName] = (isset($PROPERTY[$strFieldName]) ? htmlspecialcharsback($PROPERTY[$strFieldName]) : '');
	}
	$arProperty['PROPINFO'] = $PROPERTY['PROPINFO'];
	$arProperty['PROPINFO'] = base64_decode($arProperty['PROPINFO']);
	if (CheckSerializedData($arProperty['PROPINFO']))
	{
		$arTempo = unserialize($arProperty['PROPINFO']);
		if (is_array($arTempo))
		{
			foreach ($arTempo as $k => $v)
				$arProperty[$k] = $v;
		}
		unset($arTempo);
		unset($arProperty['PROPINFO']);
	}

	$arProperty['MULTIPLE'] = ('Y' == $arProperty['MULTIPLE'] ? 'Y' : 'N');
	$arProperty['IS_REQUIRED'] = ('Y' == $arProperty['IS_REQUIRED'] ? 'Y' : 'N');
	$arProperty['FILTRABLE'] = ('Y' == $arProperty['FILTRABLE'] ? 'Y' : 'N');
	$arProperty['SEARCHABLE'] = ('Y' == $arProperty['SEARCHABLE'] ? 'Y' : 'N');
	$arProperty['ACTIVE'] = ('Y' == $arProperty['ACTIVE'] ? 'Y' : 'N');
	$arProperty['SECTION_PROPERTY'] = ('N' == $arProperty['SECTION_PROPERTY'] ? 'N' : 'Y');
	$arProperty['SMART_FILTER'] = ('Y' == $arProperty['SMART_FILTER'] ? 'Y' : 'N');
	$arProperty['MULTIPLE_CNT'] = intval($arProperty['MULTIPLE_CNT']);
	if (0 >= $arProperty['MULTIPLE_CNT'])
		$arProperty['MULTIPLE_CNT'] = DEF_LIST_VALUE_COUNT;
	$arProperty['WITH_DESCRIPTION'] = ('Y' == $arProperty['WITH_DESCRIPTION'] ? 'Y' : 'N');

	$arProperty['USER_TYPE'] = '';
	if (false !== strpos($arProperty['PROPERTY_TYPE'],':'))
	{
		list($arProperty['PROPERTY_TYPE'],$arProperty['USER_TYPE']) = explode(':', $arProperty['PROPERTY_TYPE'], 2);
	}

	$arProperty["ID"] = $PARAMS['ID'];
}
else
{
	if($bVarsFromForm)
	{
		$arProperty = array(
			"ID" => $str_PROPERTY_ID,
			"ACTIVE" => $_POST["PROPERTY_ACTIVE"],
			"IBLOCK_ID" => $_POST["IBLOCK_ID"],
			"NAME" => $_POST["PROPERTY_NAME"],
			"SORT" => $_POST["PROPERTY_SORT"],
			"CODE" => $_POST["PROPERTY_CODE"],
			"MULTIPLE" => $_POST["PROPERTY_MULTIPLE"],
			"IS_REQUIRED" => $_POST["PROPERTY_IS_REQUIRED"],
			"SEARCHABLE" => $_POST["PROPERTY_SEARCHABLE"],
			"FILTRABLE" => $_POST["PROPERTY_FILTRABLE"],
			"WITH_DESCRIPTION" => $_POST["PROPERTY_WITH_DESCRIPTION"],
			"MULTIPLE_CNT" => $_POST["PROPERTY_MULTIPLE_CNT"],
			"HINT" => $_POST["PROPERTY_HINT"],
			"SECTION_PROPERTY" => $_POST["PROPERTY_SECTION_PROPERTY"],
			"SMART_FILTER" => $_POST["PROPERTY_SMART_FILTER"],
			"ROW_COUNT" => $_POST["PROPERTY_ROW_COUNT"],
			"COL_COUNT" => $_POST["PROPERTY_COL_COUNT"],
			"DEFAULT_VALUE" => $_POST["PROPERTY_DEFAULT_VALUE"],
			"FILE_TYPE" => $_POST["PROPERTY_FILE_TYPE"],
		);

		if (isset($_POST["PROPERTY_PROPERTY_TYPE"]))
		{
			if (strpos($_POST["PROPERTY_PROPERTY_TYPE"], ":"))
			{
				list($arProperty["PROPERTY_TYPE"], $arProperty["USER_TYPE"]) = explode(':', $_POST["PROPERTY_PROPERTY_TYPE"], 2);
			}
			else
			{
				$arProperty["PROPERTY_TYPE"] = $_POST["PROPERTY_PROPERTY_TYPE"];
			}
		}

		if(!empty($arListValues))
			$arProperty["VALUES"] = $arListValues;
	}
	elseif(is_array($arPropCheck))
	{
		$arProperty = $arPropCheck;
		if ($arProperty['PROPERTY_TYPE'] == "L")
		{
			$arProperty['VALUES'] = array();
			$rsLists = CIBlockProperty::GetPropertyEnum($arProperty['ID'],array('SORT' => 'ASC','ID' => 'ASC'));
			while($res = $rsLists->Fetch())
			{
				$arProperty['VALUES'][$res["ID"]] = array(
					'ID' => $res["ID"],
					'VALUE' => $res["VALUE"],
					'SORT' => $res['SORT'],
					'XML_ID' => $res["XML_ID"],
					'DEF' => $res['DEF'],
				);
			}
		}
		$arPropLink = CIBlockSectionPropertyLink::GetArray($intIBlockID, 0);
		if(isset($arPropLink[$arProperty["ID"]]))
		{
			$arProperty["SECTION_PROPERTY"] = "Y";
			$arProperty["SMART_FILTER"] = $arPropLink[$arProperty["ID"]]["SMART_FILTER"];
		}
		else
		{
			$arProperty["SECTION_PROPERTY"] = "N";
			$arProperty["SMART_FILTER"] = "N";
		}
	}
	else
	{
		$arProperty = $arDefPropInfo;
		$arProperty["IBLOCK_ID"] = $intIBlockID;
	}

	if (!$bSectionPopup)
	{
		$aTabs = array(
			array(
				"DIV" => "edit1",
				"TAB" => GetMessage("BT_ADM_IEP_TAB"),
				"ICON" => "iblock",
				"TITLE" => GetMessage("BT_ADM_IEP_TAB_TITLE"),
			),
		);

		$tabControl = new CAdminTabControl("tabControl", $aTabs);

		if($ID > 0)
			$APPLICATION->SetTitle(GetMessage("BT_ADM_IEP_PROPERTY_EDIT", array("#NAME#" => htmlspecialcharsbx($arProperty["NAME"]))));
		else
			$APPLICATION->SetTitle(GetMessage("BT_ADM_IEP_PROPERTY_NEW"));
	}
}

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	if ('L' == $arProperty['PROPERTY_TYPE'])
		$arDefPropInfo['MULTIPLE'] = $arProperty['MULTIPLE'];

	$arTypesList = array(
		"S" => GetMessage("BT_ADM_IEP_PROP_TYPE_S"),
		"N" => GetMessage("BT_ADM_IEP_PROP_TYPE_N"),
		"L" => GetMessage("BT_ADM_IEP_PROP_TYPE_L"),
		"F" => GetMessage("BT_ADM_IEP_PROP_TYPE_F"),
		"G" => GetMessage("BT_ADM_IEP_PROP_TYPE_G"),
		"E" => GetMessage("BT_ADM_IEP_PROP_TYPE_E"),
	);

	$aMenu = array(
		array(
			"TEXT" => GetMessage("BT_ADM_IEP_LIST") ,
			"LINK" => 'iblock_property_admin.php?lang='.LANGUAGE_ID.'&IBLOCK_ID='.$intIBlockID.($_REQUEST["admin"]=="Y"? "&admin=Y": "&admin=N"),
			"ICON" => "btn_list",
		),
	);

	if($str_PROPERTY_ID > 0)
	{
		$aMenu[] = array("SEPARATOR"=>"Y");
		$aMenu[] = array(
			"TEXT" => GetMessage("BT_ADM_IEP_DELETE") ,
			"LINK"=>"javascript:jsDelete('frm_prop', '".GetMessage("BT_ADM_IEP_CONFIRM_DEL_MESSAGE")."')",
			"ICON"=>"btn_delete",
		);
	}

	if(!$bReload)
	{
		$context = new CAdminContextMenu($aMenu);
		$context->Show();
	}

	if($strWarning)
		CAdminMessage::ShowOldStyleError($strWarning."<br>");
	elseif($message)
		echo $message->Show();

	?>
	<script type="text/javascript">
	function jsDelete(form_id, message)
	{
		var _form = BX(form_id);
		var _flag = BX('action');
		if(!!_form && !!_flag)
		{
			if(confirm(message))
			{
				_flag.value = 'delete';
				_form.submit();
			}
		}
	}
	function reloadForm()
	{
		var _form = BX('frm_prop');
		var _flag = BX('action');
		if(!!_form && !!_flag)
		{
			_flag.value = 'reload';
			<?if($bSectionPopup):?>
				BX.WindowManager.Get().PostParameters();
			<?else:?>
				_form.submit();
			<?endif?>
		}
	}
	</script>
	<form method="POST" name="frm_prop" id="frm_prop" action="<?echo $APPLICATION->GetCurPageParam(); ?>" enctype="multipart/form-data">
	<div id="form_content">
		<input type="hidden" name="ID" value="<?echo $str_PROPERTY_ID?>">
		<input type="hidden" name="IBLOCK_ID" value="<?echo $intIBlockID?>">
		<input type="hidden" name="elem_id" value="<?echo $str_elem_id?>">
		
		<input type="hidden" name="action" id="action" value="">

		<?echo bitrix_sessid_post();?>
		<input type="hidden" name="bxpublic" value="Y">
		<input type="hidden" name="save" value="Y">
		
		<table class="edit-table" width="100%"><tbody>
		
		<?
		$showKeyExist = isset($arPropertyFields["SHOW"]) && !empty($arPropertyFields["SHOW"]) && is_array($arPropertyFields["SHOW"]);
		$hideKeyExist = isset($arPropertyFields["HIDE"]) && !empty($arPropertyFields["HIDE"]) && is_array($arPropertyFields["HIDE"]);
		?>
	<?
// PROPERTY_TYPE specific properties
	if ('L' == $arProperty['PROPERTY_TYPE'])
	{?>
<tr>
	<td colspan="2" align="center">
	<table class="internal" id="list-tbl" style="margin: 0 auto;">
		<tr class="heading">
			<td><?echo GetMessage("BT_ADM_IEP_PROP_LIST_ID")?></td>
			<td><?echo GetMessage("BT_ADM_IEP_PROP_LIST_XML_ID")?></td>
			<td><?echo GetMessage("BT_ADM_IEP_PROP_LIST_VALUE")?></td>
			<td><?echo GetMessage("BT_ADM_IEP_PROP_LIST_SORT")?></td>
			<td><?echo GetMessage("BT_ADM_IEP_PROP_LIST_DEFAULT")?></td>
		</tr>
	<?
		if ('Y' != $arProperty['MULTIPLE'])
		{
			$boolDef = true;
			if (isset($arProperty['VALUES']) && is_array($arProperty['VALUES']))
			{
				foreach ($arProperty['VALUES'] as &$arListValue)
				{
					if ('Y' == $arListValue['DEF'])
					{
						$boolDef = false;
						break;
					}
				}
				unset($arListValue);
			}
		?><tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td colspan="2"><?echo GetMessage("BT_ADM_IEP_PROP_LIST_DEFAULT_NO")?></td>
		<td style="text-align:center"><input type="radio" name="PROPERTY_VALUES_DEF" value="0" <?if ($boolDef) echo " checked"; ?>> </td>
		</tr>
		<?
		}
		$MAX_NEW_ID = 0;
		if (isset($arProperty['VALUES']) && is_array($arProperty['VALUES']))
		{
			foreach ($arProperty['VALUES'] as $intKey => $arListValue)
			{
				$arPropInfo = array(
					'ID' => $intKey,
					'XML_ID' => $arListValue['XML_ID'],
					'VALUE' => $arListValue['VALUE'],
					'SORT' => (0 < intval($arListValue['SORT']) ? intval($arListValue['SORT']) : '500'),
					'DEF' => ('Y' == $arListValue['DEF'] ? 'Y' : 'N'),
					'MULTIPLE' => $arProperty['MULTIPLE'],
				);
				echo __AddListValueRow($intKey,$arPropInfo);
			}
			$MAX_NEW_ID = sizeof($arProperty['VALUES']);
		}

		for ($i = $MAX_NEW_ID; $i < $MAX_NEW_ID+DEF_LIST_VALUE_COUNT; $i++)
		{
			$intKey = 'n'.$i;
			$arPropInfo = array(
				'ID' => $intKey,
				'XML_ID' => '',
				'VALUE' => '',
				'SORT' => '500',
				'DEF' => 'N',
				'MULTIPLE' => $arProperty['MULTIPLE'],
			);
			echo __AddListValueRow($intKey,$arPropInfo);
		}
		?>
		</table>
		<div style="width: 100%; text-align: center; margin: 10px 0;">
			<input class="adm-btn-big" type="button" id="propedit_add_btn" name="propedit_add" value="<?echo GetMessage("BT_ADM_IEP_PROP_LIST_MORE")?>">
		</div>
		<input type="hidden" name="PROPERTY_CNT" id="PROPERTY_CNT" value="<?echo ($MAX_NEW_ID+DEF_LIST_VALUE_COUNT)?>">
		</td>
</tr><?
	}
	elseif ("F" == $arProperty['PROPERTY_TYPE'])
	{
		$bShow = true;
		if ($showKeyExist && in_array("COL_COUNT", $arPropertyFields["SHOW"]))
			$bShow = true;
		elseif ($hideKeyExist && in_array("COL_COUNT", $arPropertyFields["HIDE"]))
			$bShow = false;

		if ($bShow)
		{
			?><tr>
			<td width="40%"><?echo GetMessage("BT_ADM_IEP_PROP_FILE_TYPES_COL_CNT")?></td>
			<td><input type="text" size="2" maxlength="10" name="PROPERTY_COL_COUNT" value="<?echo intval($arProperty['COL_COUNT'])?>"></td>
			</tr><?
		}
		elseif(
			isset($arPropertyFields["SET"]["COL_COUNT"])
		)
		{
			?>
			<input type="hidden" name="PROPERTY_COL_COUNT" value="<?echo htmlspecialcharsbx($arPropertyFields["SET"]["COL_COUNT"])?>">
			<?
		}
		?>
<tr>
	<td width="40%"><?echo GetMessage("BT_ADM_IEP_PROP_FILE_TYPES")?></td>
	<td>
		<input type="text"  size="50" maxlength="255" name="PROPERTY_FILE_TYPE" value="<?echo htmlspecialcharsbx($arProperty['FILE_TYPE']); ?>" id="CURRENT_PROPERTY_FILE_TYPE">
		<select  onchange="if(this.selectedIndex!=0) document.getElementById('CURRENT_PROPERTY_FILE_TYPE').value=this[this.selectedIndex].value">
			<option value="-"></option>
			<option value=""<?if('' == $arProperty['FILE_TYPE'])echo " selected"?>><?echo GetMessage("BT_ADM_IEP_PROP_FILE_TYPES_ANY")?></option>
			<option value="jpg, gif, bmp, png, jpeg"<?if("jpg, gif, bmp, png, jpeg" == $arProperty['FILE_TYPE'])echo " selected"?>><?echo GetMessage("BT_ADM_IEP_PROP_FILE_TYPES_PIC")?></option>
			<option value="mp3, wav, midi, snd, au, wma"<?if("mp3, wav, midi, snd, au, wma" == $arProperty['FILE_TYPE'])echo " selected"?>><?echo GetMessage("BT_ADM_IEP_PROP_FILE_TYPES_SOUND")?></option>
			<option value="mpg, avi, wmv, mpeg, mpe, flv"<?if("mpg, avi, wmv, mpeg, mpe, flv" == $arProperty['FILE_TYPE'])echo " selected"?>><?echo GetMessage("BT_ADM_IEP_PROP_FILE_TYPES_VIDEO")?></option>
			<option value="doc, txt, rtf"<?if("doc, txt, rtf" == $arProperty['FILE_TYPE'])echo " selected"?>><?echo GetMessage("BT_ADM_IEP_PROP_FILE_TYPES_DOCS")?></option>
		</select>
	</td>
</tr>
<?
	}
	elseif ("G" == $arProperty['PROPERTY_TYPE'] || "E" == $arProperty['PROPERTY_TYPE'])
	{
		$bShow = false;
		if ($showKeyExist && in_array("COL_COUNT", $arPropertyFields["SHOW"]))
		{
			$bShow = true;
		}

		if ($bShow)
		{
			?>
			<tr>
			<td width="40%"><?echo GetMessage("BT_ADM_IEP_PROP_FILE_TYPES_COL_CNT")?></td>
			<td><input type="text" size="2" maxlength="10" name="PROPERTY_COL_COUNT" value="<?echo intval($arProperty['COL_COUNT']);?>"></td>
			</tr>
			<?
		}
		elseif(
			isset($arPropertyFields["SET"]["COL_COUNT"])
		)
		{
			?>
			<input type="hidden" name="PROPERTY_COL_COUNT" value="<?echo htmlspecialcharsbx($arPropertyFields["SET"]["COL_COUNT"])?>">
			<?
		}
		?>
	<tr>
		<td width="40%"><?echo GetMessage("BT_ADM_IEP_PROP_LINK_IBLOCK")?></td>
		<td>
		<?
		$b_f = ($arProperty['PROPERTY_TYPE']=="G" || ($arProperty['PROPERTY_TYPE'] == 'E' && $arProperty['USER_TYPE'] == BT_UT_SKU_CODE) ? array("!ID"=>$intIBlockID) : array());
		echo GetIBlockDropDownList(
			$arProperty['LINK_IBLOCK_ID'],
			"PROPERTY_LINK_IBLOCK_TYPE_ID",
			"PROPERTY_LINK_IBLOCK_ID",
			$b_f,
			'class="adm-detail-iblock-types"',
			'class="adm-detail-iblock-list"'
		);
		?>
		</td>
	</tr>
	<?}
	else
	{
		$bShow = true;
		if ($hideKeyExist && in_array("COL_COUNT", $arPropertyFields["HIDE"]))
			$bShow = false;
		elseif ($hideKeyExist && in_array("ROW_COUNT", $arPropertyFields["HIDE"]))
			$bShow = false;

		if ($bShow)
		{?><tr>
			<td width="40%"><?echo GetMessage("BT_ADM_IEP_PROP_SIZE")?></td>
			<td>
				<input type="text"  size="2" maxlength="10" name="PROPERTY_ROW_COUNT" value="<?echo intval($arProperty['ROW_COUNT']); ?>"> x <input type="text"  size="2" maxlength="10" name="PROPERTY_COL_COUNT" value="<?echo intval($arProperty['COL_COUNT']); ?>">
			</td>
		</tr>
		<?}
		else
		{
			if (isset($arPropertyFields["SET"]["ROW_COUNT"]))
			{?><input type="hidden" name="PROPERTY_ROW_COUNT" value="<?echo htmlspecialcharsbx($arPropertyFields["SET"]["ROW_COUNT"])?>"><?}
			else
			{?><input type="hidden" name="PROPERTY_ROW_COUNT" value="<?echo intval($arProperty['ROW_COUNT'])?>"><?}

			if(isset($arPropertyFields["SET"]["COL_COUNT"]))
			{?><input type="hidden" name="PROPERTY_COL_COUNT" value="<?echo htmlspecialcharsbx($arPropertyFields["SET"]["COL_COUNT"])?>"><? }
			else
			{ ?><input type="hidden" name="PROPERTY_COL_COUNT" value="<?echo intval($arProperty['COL_COUNT']); ?>"><? }
		}

		$bShow = true;
		if ($hideKeyExist && in_array("DEFAULT_VALUE", $arPropertyFields["HIDE"]))
			$bShow = false;

		if ($bShow)
		{?><tr>
			<td width="40%"><?echo GetMessage("BT_ADM_IEP_PROP_DEFAULT")?></td>
			<td>
			<?if(array_key_exists("GetPropertyFieldHtml", $arUserType))
			{
				echo call_user_func_array($arUserType["GetPropertyFieldHtml"],
					array(
						$arProperty,
						array(
							"VALUE"=>$arProperty["DEFAULT_VALUE"],
							"DESCRIPTION"=>""
						),
						array(
							"VALUE"=>"PROPERTY_DEFAULT_VALUE",
							"DESCRIPTION"=>"",
							"MODE" => "EDIT_FORM",
							"FORM_NAME" => "frm_prop"
						),
					));
			}
			else
			{
				?><input type="text"  size="50" maxlength="2000" name="PROPERTY_DEFAULT_VALUE" value="<?echo htmlspecialcharsbx($arProperty['DEFAULT_VALUE']);?>"><?
			}
		?></td>
	</tr><?
		}
	}
	if ($USER_TYPE_SETTINGS_HTML)
	{?><tr class="heading"><td colspan="2"><?
		echo (isset($arPropertyFields["USER_TYPE_SETTINGS_TITLE"]) && '' != trim($arPropertyFields["USER_TYPE_SETTINGS_TITLE"]) ? $arPropertyFields["USER_TYPE_SETTINGS_TITLE"] : GetMessage("BT_ADM_IEP_PROP_USER_TYPE_SETTINGS"));
		?></td></tr><?
		echo $USER_TYPE_SETTINGS_HTML;
	}

	if(is_object($tabControl))
	{
		if (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1):
			$tabControl->Buttons(array(
				"disabled"=>false,
				"back_url"=>'iblock_property_admin.php?lang='.LANGUAGE_ID.'&IBLOCK_ID='.$intIBlockID.($_REQUEST["admin"]=="Y"? "&admin=Y": "&admin=N"),
			));
		else:
			$tabControl->ButtonsPublic(array(
				'.btnSave',
				'.btnCancel'
			));
		endif;
		$tabControl->End();
	}
	else
	{
		?></tbody></table><?
	}
	?></div></form>
<script type="text/javascript"><?
	if('L' == $arProperty['PROPERTY_TYPE'])
	{
?>
window.oPropSet = {
		pTypeTbl: BX("list-tbl"),
		curCount: <? echo ($MAX_NEW_ID+5); ?>,
		intCounter: BX("PROPERTY_CNT")
	};

function add_list_row()
{
	var id = window.oPropSet.curCount++;
	window.oPropSet.intCounter.value = window.oPropSet.curCount;
	var newRow = window.oPropSet.pTypeTbl.insertRow(window.oPropSet.pTypeTbl.rows.length);

	var oCell = newRow.insertCell(-1);
	var strContent = '<? echo CUtil::JSEscape(__AddListValueIDCell('ntmp_xxx',$arDefPropInfo)); ?>';
	strContent = strContent.replace(/tmp_xxx/ig, id);
	oCell.innerHTML = strContent;
	var oCell = newRow.insertCell(-1);
	var strContent = '<? echo CUtil::JSEscape(__AddListValueXmlIDCell('ntmp_xxx',$arDefPropInfo)); ?>';
	strContent = strContent.replace(/tmp_xxx/ig, id);
	oCell.innerHTML = strContent;
	var oCell = newRow.insertCell(-1);
	var strContent = '<? echo CUtil::JSEscape(__AddListValueValueCell('ntmp_xxx',$arDefPropInfo)); ?>';
	strContent = strContent.replace(/tmp_xxx/ig, id);
	oCell.innerHTML = strContent;
	var oCell = newRow.insertCell(-1);
	var strContent = '<? echo CUtil::JSEscape(__AddListValueSortCell('ntmp_xxx',$arDefPropInfo)); ?>';
	strContent = strContent.replace(/tmp_xxx/ig, id);
	oCell.innerHTML = strContent;
	var oCell = newRow.insertCell(-1);
	var strContent = '<? echo CUtil::JSEscape(__AddListValueDefCell('ntmp_xxx',$arDefPropInfo)); ?>';
	strContent = strContent.replace(/tmp_xxx/ig, id);
	oCell.innerHTML = strContent;
	oCell.setAttribute('align','center');
	BX.style(oCell, 'textAlign', 'center');
	BX.adminFormTools.modifyFormElements('frm_prop');
}

var obListBtn = BX('propedit_add_btn');

if (!!obListBtn && !!window.oPropSet)
	BX.bind(obListBtn, 'click', add_list_row);
<?
	}
if($bReload && $bSectionPopup)
{
?>
setTimeout(function(){
	BX.WindowManager.Get().SetButtons([BX.CDialog.btnSave, BX.CDialog.btnCancel]);
}, 10);
<?
}
?>
(function(){

	var tbl = BX.findChild(BX("frm_prop"), {tag: 'table', className: 'edit-table'}, true, false);
	if (!tbl)
		return;

	var n = tbl.tBodies[0].rows.length;
	for(var i=0; i<n; i++)
	{
		if(tbl.tBodies[0].rows[i].cells.length > 1)
		{
			BX.addClass(tbl.rows[i].cells[0], 'adm-detail-content-cell-l');
			BX.addClass(tbl.rows[i].cells[1], 'adm-detail-content-cell-r');
		}
	}

	BX.adminFormTools.modifyFormElements('frm_prop');

})();

</script><?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>