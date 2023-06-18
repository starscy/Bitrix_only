<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Резюме");
?><?$APPLICATION->IncludeComponent("bitrix:iblock.element.add", ".default", array(
	"NAV_ON_PAGE" => "10",
	"USE_CAPTCHA" => "Y",
	"USER_MESSAGE_ADD" => "Ваше резюме добавлено",
	"USER_MESSAGE_EDIT" => "Ваше резюме сохранено",
	"DEFAULT_INPUT_SIZE" => "30",
	"RESIZE_IMAGES" => "N",
	"IBLOCK_TYPE" => "job",
	"IBLOCK_ID" => "6",
	"PROPERTY_CODES" => array(
		0 => "NAME",
		1 => "DATE_ACTIVE_TO",
		2 => "IBLOCK_SECTION",
		3 => "DETAIL_TEXT",
		4 => "27",5 => "28",6 => "29",7 => "30",8 => "31",9 => "32",10 => "33",11 => "34",12 => "35",13 => "36",14 => "37",15 => "38",16 => "39",
	),
	"PROPERTY_CODES_REQUIRED" => array(
		0 => "NAME",
		1 => "DATE_ACTIVE_TO",
		2 => "IBLOCK_SECTION",
		3 => "27", 4 => "28",  5 => "29",  6 => "39", 
	),
	"GROUPS" => array(
		0 => "1",
		1 => "6",
	),
	"STATUS" => "ANY",	"STATUS_NEW" => "N",
	"ALLOW_EDIT" => "Y",
	"ALLOW_DELETE" => "Y",
	"ELEMENT_ASSOC" => "CREATED_BY",
	"MAX_USER_ENTRIES" => "5",
	"MAX_LEVELS" => "1",
	"LEVEL_LAST" => "Y",
	"MAX_FILE_SIZE" => "0",
	"PREVIEW_TEXT_USE_HTML_EDITOR" => "N",
	"DETAIL_TEXT_USE_HTML_EDITOR" => "N",
	"SEF_MODE" => "N",
	"SEF_FOLDER" => "/job/vacancy/my/",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CUSTOM_TITLE_NAME" => "Требуемая работа",
	"CUSTOM_TITLE_TAGS" => "",
	"CUSTOM_TITLE_DATE_ACTIVE_FROM" => "",
	"CUSTOM_TITLE_DATE_ACTIVE_TO" => "Срок публикации до",
	"CUSTOM_TITLE_IBLOCK_SECTION" => "Категория",
	"CUSTOM_TITLE_PREVIEW_TEXT" => "",
	"CUSTOM_TITLE_PREVIEW_PICTURE" => "",
	"CUSTOM_TITLE_DETAIL_TEXT" => "Дополнительно",
	"CUSTOM_TITLE_DETAIL_PICTURE" => "",
	"SEND_EMAIL" => "Y",
	"EMAIL_TO" => "test@mail.ru",
	"SUBJECT" => "Добавлено новое резюме",
	"EVENT_MESSAGE_ID" => array(),
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
