<?
use Bitrix\Main\Text\HtmlFilter;
use CUserTypeManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Site\Dadata\Option;

class EMBXUserTypeStore extends \Bitrix\Main\UserField\Types\EnumType {
	
	public const
		USER_TYPE_ID = 'EMBXStore',
		DISPLAYS = [
				self::DISPLAY_LIST,
				self::DISPLAY_CHECKBOX,
		];
	
	public static function getDescription(): array {
		return [
				'DESCRIPTION' => Loc::getMessage('EMBXPA_USER_TYPE_STORE_DESCRIPTION'),
				'BASE_TYPE' => CUserTypeManager::BASE_TYPE_ENUM,
		];
	}		

	public static function getSettingsHtml($userField, ?array $additionalParameters, $varsFromForm): string {
		$result = '';

		if ($varsFromForm) {
			$ACTIVE_FILTER = $GLOBALS[$additionalParameters["NAME"]]["ACTIVE_FILTER"] === "Y" ? "Y" : "N";
		} elseif (is_array($userField)) {
			$ACTIVE_FILTER = $userField["SETTINGS"]["ACTIVE_FILTER"] === "Y" ? "Y" : "N";
		} else {
			$ACTIVE_FILTER = "N";
		}

		if ($varsFromForm) {
			$value = $GLOBALS[$additionalParameters["NAME"]]["DEFAULT_VALUE"];
		} elseif (is_array($userField)) {
			$value = $userField["SETTINGS"]["DEFAULT_VALUE"];
		} else {
			$value = "";
		}

		if (\Bitrix\Main\Loader::includeModule('catalog')) {
			$result .= '
			<tr>
				<td>' . Loc::getMessage("USER_TYPE_IBEL_DEFAULT_VALUE") . ':</td>
				<td>
					<select name="' . $additionalParameters["NAME"] . '[DEFAULT_VALUE]" size="1">
						<option value="">' . Loc::getMessage("EMBXPA_NO_VALUE") . '</option>
			';			
			$rs = self::getList(empty($userField) ? []: $userField);
			while ($ar = $rs->GetNext() ) {
				$result .= '<option value="' . $ar["ID"] . '"' . ($ar["ID"] == $value ? " selected" : "") . '>' . $ar["VALUE"] . '</option>';
			}
			$result .= '</select>';
		} else {
			$result .= '
			<tr>
				<td>' . Loc::getMessage("EMBXPA_NO_VALUE") . ':</td>
				<td>
					<input type="text" size="8" name="' . $additionalParameters["NAME"] . '[DEFAULT_VALUE]" value="' . htmlspecialcharsbx($value) . '">
				</td>
			</tr>
			';
		}

		if ($varsFromForm) {
			$value = $GLOBALS[$additionalParameters["NAME"]]["DISPLAY"];
		} elseif (is_array($userField)) {
			$value = $userField["SETTINGS"]["DISPLAY"];
		} else {
			$value = "LIST";
		}

		$result .= '
		<tr>
			<td class="adm-detail-valign-top">' . Loc::getMessage("USER_TYPE_ENUM_DISPLAY") . ':</td>
			<td>';
		
		foreach(self::DISPLAYS as $display){
			$result .= '<label><input type="radio" name="' . $additionalParameters["NAME"] . '[DISPLAY]" value="' . $display . '" ' . ($display == $value ? 'checked="checked"' : '') . '>' . Loc::getMessage("USER_TYPE_IBEL_" . $display) . '</label><br>';
		}
		$result .= '
			</td>
		</tr>
		';

		if ($varsFromForm) {
			$value = intval($GLOBALS[$additionalParameters["NAME"]]["LIST_HEIGHT"]);
		} elseif (is_array($userField)) {
			$value = intval($userField["SETTINGS"]["LIST_HEIGHT"]);
		} else {
			$value = 1;
		}
		$result .= '
		<tr>
			<td>' . Loc::getMessage("USER_TYPE_IBEL_LIST_HEIGHT") . ':</td>
			<td>
				<input type="text" name="' . $additionalParameters["NAME"] . '[LIST_HEIGHT]" size="10" value="' . $value . '">
			</td>
		</tr>
		';

		$result .= '
		<tr>
			<td>' . Loc::getMessage("USER_TYPE_IBEL_ACTIVE_FILTER") . ':</td>
			<td>
				<input type="checkbox" name="' . $additionalParameters["NAME"] . '[ACTIVE_FILTER]" value="Y" ' . ($ACTIVE_FILTER=="Y"? 'checked="checked"': '').'>
			</td>
		</tr>
		';
											
		return $result;
	}
	
	/**
	 * @param array $userField An array describing the field.
	 * @param array $additionalParameters An array of controls from the form. Contains the elements NAME and VALUE.
	 * @return string HTML
	 */
	public static function renderAdminListView(array $userField, ?array $additionalParameters): string {
		if(empty($additionalParameters["VALUE"])){
			return "";
		}
		
		if(empty($userField["VALUE"])){
			$userField["VALUE"] = $additionalParameters["VALUE"];
			
			$result = self::getList($userField, $additionalParameters["VALUE"])->fetch();
			if($result){
				return $result["VALUE"];
			}
		}

		return "";
	}
	
	/**
	 * @param array $userField
	 * @return bool|CDBResult
	 */
	public static function getList(array $userField, $id = null) {
		if(!\Bitrix\Main\Loader::includeModule('catalog')){
			return false;
		}
		
		$filter = [];
		
		if(	isset($userField["SETTINGS"]["ACTIVE_FILTER"]) && $userField["SETTINGS"]["ACTIVE_FILTER"] == "Y"){
			$filter["ACTIVE"] = "Y";
		}
		
		if(!empty($id)){
			$filter["ID"] = $id;
		}

		$res = \Bitrix\Catalog\StoreTable::getList([
				"order" => ["ID" => "ASC", "TITLE" => "ASC"],
				"filter" => $filter,
		])->fetchAll();
		
		$result = [];
		foreach ($res as $item){
			$result[] = [
					"ID" => $item["ID"],
					"VALUE" => "[" . $item["ID"] . "] " . $item["TITLE"]
			];
		}
		
		$dbRes = new \CDBResult();
		$dbRes->InitFromArray($result);
		
		return $dbRes;
	}
		
	/**
	 * This function is called before saving the property metadata to the database.
	 *
	 * It should 'clear' the array with the settings of the instance of the property type.
	 * In order to accidentally / intentionally no one wrote down any garbage there.
	 *
	 * @param array $userField An array describing the field. Warning! this description of the field has not yet been saved to the database!
	 * @return array An array that will later be serialized and stored in the database.
	 */
	public static function prepareSettings(array $userField): array {
		$display = $userField['SETTINGS']['DISPLAY'];
		$height = (int)$userField['SETTINGS']['LIST_HEIGHT'];
		$activeFilter = ($userField['SETTINGS']['ACTIVE_FILTER'] === 'N' ? 'N' : 'Y');
		
		if (!in_array($display, self::DISPLAYS, true)) {
			$display = self::DISPLAY_LIST;
		}
		
		return [
				'DISPLAY' => $display,
				'LIST_HEIGHT' => ($height < 1 ? 1 : $height),
				'ACTIVE_FILTER' => $activeFilter,
				'DEFAULT_VALUE' => $userField['SETTINGS']['DEFAULT_VALUE'],
		];
	}
}
?>