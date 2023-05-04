<?php 
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use \Bitrix\Main\Page\Asset;

$asset = Asset::getInstance();

$asset->addCss(SITE_TEMPLATE_PATH . '/assets/css/common.css' );
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <title><?php $APPLICATION->ShowTitle();?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <link rel="shortcut icon" href="<?=SITE_TEMPLATE_PATH?>/assets/images/favicon.604825ed.ico" type="image/x-icon">
     <?php $APPLICATION->ShowHead();?>
</head>
<body>
<?php $APPLICATION->ShowPanel();?>
<header>
</header>


