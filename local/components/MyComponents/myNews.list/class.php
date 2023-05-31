<?php

namespace MyComponents\components;

use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

class MyNewsList extends \CBitrixComponent implements Errorable
{
    public array $arrFilter = [];

    protected ErrorCollection $errorCollection;

    public function onPrepareComponentParams($arParams)
    {
        $this->errorCollection = new ErrorCollection();

        if(!isset($arParams["CACHE_TIME"]))
            $arParams["CACHE_TIME"] = 36000000;

        $arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
        $arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"]);

        $arParams["SORT_BY1"] = trim($arParams["SORT_BY1"]);
        if($arParams["SORT_BY1"] == '')
            $arParams["SORT_BY1"] = "ACTIVE_FROM";
        if(!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER1"]))
            $arParams["SORT_ORDER1"]="DESC";
        if($arParams["SORT_BY2"] == '') {
            if (mb_strtoupper($arParams["SORT_BY1"]) == 'SORT') {
                $arParams["SORT_BY2"] = "ID";
                $arParams["SORT_ORDER2"] = "DESC";
            } else {
                $arParams["SORT_BY2"] = "SORT";
            }
        }
        if(!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER2"]))
            $arParams["SORT_ORDER2"]="ASC";

        if($arParams["FILTER_NAME"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
        {
            $this->arrFilter = [];
        }
        else
        {
            $this->arrFilter = $GLOBALS[$arParams["FILTER_NAME"]];
        }

        return $arParams;
    }

    public function executeComponent()
    {
        try {
            $this->checkModules();
            $this->getNews();
        } catch (\Exception $e) {
            ShowError($e->getMessage());
        }
    }

    public function getErrors():array
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code): Error
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    protected function checkModules()
    {
        if (!Loader::includeModule('iblock'))
            throw new SystemException(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
    }

    protected function getNews()
    {
        if ($this->startResultCache()) {
            if (is_numeric($this->arParams["IBLOCK_ID"])) {
                $rsIBlock = \CIBlock::GetList([], [
                    "ACTIVE" => "Y",
                    "ID" => $this->arParams["IBLOCK_ID"],
                ]);
            } else {
                $rsIBlock = \CIBlock::GetList([], [
                    "ACTIVE" => "Y",
                    "TYPE" => $this->arParams["IBLOCK_TYPE"],
                ]);
            }

            while ($block = $rsIBlock->GetNext()) {
                $this->arResult['ID'][] = $block['ID'];
            }

            if(!$this->arResult) {
                $this->abortResultCache();
                return;
            }

            //select
            $arSelect = array_merge($this->arParams["FIELD_CODE"], [ 'ID', 'NAME', 'IBLOCK_ID', 'ACTIVE_FROM', 'PREVIEW_TEXT'] );
            $bGetProperty = !empty($arParams["PROPERTY_CODE"]);

            //where
            $arFilter = array_merge($this->arrFilter,[
                "IBLOCK_TYPE" => $this->arParams['IBLOCK_TYPE'],
                "IBLOCK_ID" => $this->arResult['ID'],
                "ACTIVE" => "Y",
               // 'PROPERTY_SEX' => "m",
            ]);

            //order
            $arSort = [
                $this->arParams["SORT_BY1"]=>$this->arParams["SORT_ORDER1"],
                $this->arParams["SORT_BY2"]=>$this->arParams["SORT_ORDER2"],
            ];

            $this->arResult['ITEMS'] = [];

            $rsElement = \CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
            while ($row = $rsElement->GetNext()) {
                $id = (int)$row['ID'];
                $row['PROPERTIES'] = [];
                $this->arResult["ITEMS"][$row['IBLOCK_ID']][$id] = $row;
                unset($row);
            }

            if (!empty($this->arResult['ITEMS'])) {
                foreach ($this->arResult['ITEMS'] as $key=>&$arBlock) {
                    \CIBlockElement::GetPropertyValuesArray($arBlock,$key, ['IBLOCK_ID' => $key]);
                }
            }

            $this->SetResultCacheKeys([]);
            $this->IncludeComponentTemplate();

//            $rsElementProps = \CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
//
//            $bGetProperty = !empty($this->arParams["PROPERTY_CODE"]);
//            if ($bGetProperty) {
//                foreach ($this->arResult["ITEMS"] as &$arBlock) {
//                    foreach ($arBlock as &$arItem) {
//                        if ($row = $rsElementProps->GetNextElement()) {
//                            $props = $row->GetProperties();
//                            $fields = $row->GetFields();
//                            $arItem['DISPLAY_PROPERTIES'] = $props;
//                            $arItem['FIELDS'] = $fields;
//                        }
//                    }
//                }
//            }
        }
    }
}