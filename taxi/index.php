<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("taxi");
?>

<?php

$APPLICATION->IncludeComponent(
	"myComponents:getFreeCar",
	".default",
	[
		"IBLOCK_ID" => "9",


	]);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>