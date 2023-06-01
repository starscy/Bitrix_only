<?php

function getIDBlockByCode (string $code) :int
{
    $strCode = trim($code) ;
    CModule::IncludeModule("iblock");
    $rsIBlock = CIBlock::GetList([], [
        "ACTIVE" => 'Y',
        "CODE" => $code,
        ['CODE', 'ID']
    ]);

    $arResult = $rsIBlock->fetch();


    if(!$arResult['ID']) {
        throw new Exception('Инфоблок с данными ' . $code .' не найден');
    }

    return (int)$arResult['ID'];
}