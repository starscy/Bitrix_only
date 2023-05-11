<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Форма");
?>
    <div class="contact-form">
        <div class="contact-form__head">
            <div class="contact-form__head-title">Связаться</div>
            <div class="contact-form__head-text">Наши сотрудники помогут выполнить подбор услуги и&nbsp;расчет цены с&nbsp;учетом
                ваших требований
            </div>
        </div>
        <?$APPLICATION->IncludeComponent(
	"MyComponents:form.result.new", 
	"form_application_only", 
	array(
		"CACHE_TIME" => "3600",
		"CACHE_TYPE" => "A",
		"CHAIN_ITEM_LINK" => "",
		"CHAIN_ITEM_TEXT" => "",
		"EDIT_URL" => "",
		"IGNORE_CUSTOM_TEMPLATE" => "Y",
		"LIST_URL" => "",
		"SEF_MODE" => "Y",
		"SUCCESS_URL" => "/form/success.php",
		"USE_EXTENDED_ERRORS" => "N",
		"WEB_FORM_ID" => "5",
		"COMPONENT_TEMPLATE" => "form_application_only",
		"SEF_FOLDER" => "/form/"
	),
	false
);?>
    </div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>