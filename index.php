<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Главная страница");
?><?$APPLICATION->IncludeComponent(
    "MyComponents:news.list",
    "best_offers_template",
    Array()
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>