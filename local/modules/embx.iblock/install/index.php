<?
global $MESS;
$PathInstall= str_replace("\\", "/", __FILE__);
$PathInstall= dirname($PathInstall);
IncludeModuleLangFile(dirname($PathInstall) . "/include.php");
if(is_file($PathInstall.'/version.php')){
	include($PathInstall.'/version.php');
}

if (class_exists("embx.iblock"))
	return;

class embx_iblock extends CModule {
	var $MODULE_ID= "embx.iblock";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS= "Y";
	var $SHOW_SUPER_ADMIN_GROUP_RIGHTS= "Y";
	var $NEED_MAIN_VERSION = '17.0.0';
	var $NEED_MODULES = array('main', 'iblock');

	public function __construct() {
		$arModuleVersion= array ();

		$path= str_replace("\\", "/", __FILE__);
		$path= substr($path, 0, strlen($path) - strlen("/index.php"));

		include ($path . "/version.php");
		$this->MODULE_VERSION= $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE= $arModuleVersion["VERSION_DATE"];

		$this->PARTNER_URI = GetMessage("EMBX_INSTALL_MODULE_PARTNER_URL");
		$this->PARTNER_NAME = GetMessage("EMBX_INSTALL_MODULE_PARTNER_NAME");
		$this->MODULE_NAME = GetMessage("EMBX_INSTALL_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("EMBX_INSTALL_MODULE_DESCRIPTION");
	}

	function DoInstall() {
		global $APPLICATION;

		if (is_array($this->NEED_MODULES) && !empty($this->NEED_MODULES))
			foreach ($this->NEED_MODULES as $module)
			if (!IsModuleInstalled($module))
			$this->ShowForm('ERROR', GetMessage('EMBX_NEED_MODULES', array('#MODULE#' => $module)));

		$_RIGHT= $APPLICATION->GetGroupRight($this->MODULE_ID);
		if ($_RIGHT == "W") {

			if (strlen($this->NEED_MAIN_VERSION)<=0 || version_compare(SM_VERSION, $this->NEED_MAIN_VERSION)>=0) {
				$this->InstallFiles();
				$this->InstallModule();

				$this->ShowForm('OK', GetMessage('EMBX_INSTALL_OK'));
			}
			else
				$this->ShowForm('ERROR', GetMessage('EMBX_NEED_RIGHT_VER', array('#NEED#' => $this->NEED_MAIN_VERSION)));
		}
	}

	function InstallModule(){
		RegisterModule($this->MODULE_ID);
		
		RegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, "EMBXPropertyAdvElementList", "GetPropertyDescription");
		if(CModule::IncludeModule("sale")){
			RegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, "EMBXPropertyDeliveries", "GetPropertyDescription");
			RegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, "EMBXPropertyStore", "GetPropertyDescription");
			RegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, "EMBXPropertyLocations", "GetPropertyDescription");
			RegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, "EMBXPropertyPS", "GetPropertyDescription");
			RegisterModuleDependences("main", "OnUserTypeBuildList", $this->MODULE_ID, "EMBXUserTypePS", "GetUserTypeDescription");
			RegisterModuleDependences("main", "OnUserTypeBuildList", $this->MODULE_ID, "EMBXUserTypeDelivery", "GetUserTypeDescription");
		}
		
		if(CModule::IncludeModule("catalog")){
		    RegisterModuleDependences("main", "OnUserTypeBuildList", $this->MODULE_ID, "EMBXUserTypeStore", "GetUserTypeDescription");
		}
				
		return true;
	}

	function InstallFiles() {

		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);

		return true;
	}

	function DoUninstall() {
		global $APPLICATION, $DB, $errors, $step;
		$_RIGHT= $APPLICATION->GetGroupRight($this->MODULE_ID);
		if ($_RIGHT == "W") {

			$this->UnInstallFiles();
			$this->UnInstallModule();

			$this->ShowForm('OK', GetMessage('EMBX_INSTALL_DEL'));
		}
	}

	function UnInstallModule(){
		UnRegisterModule($this->MODULE_ID);

		COption::RemoveOption($this->MODULE_ID);
		UnRegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, "EMBXPropertyAdvElementList", "GetPropertyDescription");
		
		if(CModule::IncludeModule("sale")){
			UnRegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, "EMBXPropertyDeliveries", "GetPropertyDescription");
			UnRegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, "EMBXPropertyStore", "GetPropertyDescription");
			UnRegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, "EMBXPropertyLocations", "GetPropertyDescription");
			UnRegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, "EMBXPropertyPS", "GetPropertyDescription");
			UnRegisterModuleDependences("main", "OnUserTypeBuildList", $this->MODULE_ID, "EMBXUserTypePS", "GetUserTypeDescription");
			UnRegisterModuleDependences("main", "OnUserTypeBuildList", $this->MODULE_ID, "EMBXUserTypeDelivery", "GetUserTypeDescription");
		}
		
		if(CModule::IncludeModule("catalog")){
		    UnRegisterModuleDependences("main", "OnUserTypeBuildList", $this->MODULE_ID, "EMBXUserTypeStore", "GetUserTypeDescription");
		}

		return true;
	}

	function UnInstallFiles($arParams= array ()) {
		global $DB;

		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");

		return true;
	}

	private function ShowForm($type, $message, $buttonName='') {
		global $APPLICATION;
		
		$keys = array_keys($GLOBALS);

		for($i=0; $i<count($keys); $i++){
			if($keys[$i]!='i' && $keys[$i]!='GLOBALS' && $keys[$i]!='strTitle' && $keys[$i]!='filepath')
				global ${
				$keys[$i]};
		}

		$APPLICATION->SetTitle(GetMessage('EMBX_INSTALL_MODULE_NAME'));
		include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

		echo CAdminMessage::ShowMessage(array('MESSAGE' => $message, 'TYPE' => $type));

		?>
			<form action="<?= $APPLICATION->GetCurPage()?>" method="get">
				<p>
					<input type="hidden" name="lang" value="<?= LANG?>" />
					<input type="submit" value="<?= strlen($buttonName) ? $buttonName : GetMessage('MOD_BACK')?>" />
				</p>
			</form>
			<?
			include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
			die();
		}

	function GetModuleRightList() {
		$arr= array (
			"reference_id" => array (
				"D",
				"W"
			),
			"reference" => array (
				"[D] " . GetMessage("EMBX_DENIED"),
				"[W] " . GetMessage("EMBX_ADMIN")));
		return $arr;
	}
}
?>