<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новый раздел");
?><?$APPLICATION->IncludeComponent(
	"myComponents:form.result.new",
	".default",
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"WEB_FORM_ID" => "1",
		"IGNORE_CUSTOM_TEMPLATE" => "N",
		"USE_EXTENDED_ERRORS" => "N",
		"SEF_MODE" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"LIST_URL" => "result_list.php",
		"EDIT_URL" => "",
		"SUCCESS_URL" => "",
		"CHAIN_ITEM_TEXT" => "",
		"CHAIN_ITEM_LINK" => "",
		"VARIABLE_ALIASES" => array(
			"WEB_FORM_ID" => "WEB_FORM_ID",
			"RESULT_ID" => "RESULT_ID",
		)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>